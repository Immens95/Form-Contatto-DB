<?php
add_action('rest_api_init', function () {
    register_rest_route('fsdb/v1', '/send', [
        'methods' => 'POST',
        'callback' => 'fsdb_handle_form',
        'permission_callback' => '__return_true'
    ]);
});

function fsdb_handle_form($request) {
    $data = $request->get_json_params();

    // Get fields configuration and submitted form data
    // _fieldsConfig is expected to be an array of field objects (like from block attributes)
    // formData is expected to be a key-value map of submitted form data e.g. {'field_name_1': 'value1', ...}
    $fields_config = $data['_fieldsConfig'] ?? [];
    $form_data = $data['formData'] ?? [];

    if (empty($form_data)) {
        return new WP_REST_Response(['success' => false, 'message' => 'Nessun dato inviato.'], 400);
    }

    $sanitized_data = [];
    $email_body_parts = [];
    $db_data = []; // For main DB columns + other_data

    // Create a map of field configurations for easy lookup by name
    $fields_config_map = [];
    if (!empty($fields_config) && is_array($fields_config)) {
        foreach ($fields_config as $field_setting) {
            if (isset($field_setting['name'])) {
                $fields_config_map[sanitize_key($field_setting['name'])] = $field_setting;
            }
        }
    }

    // Validation (only if fields_config is available)
    if (!empty($fields_config_map)) {
        foreach ($fields_config_map as $field_name_key => $field_setting) {
            // $field_name = $field_setting['name']; // Already sanitized as $field_name_key
            $field_label = esc_html($field_setting['label'] ?? $field_name_key);
            $is_required = !empty($field_setting['isRequired']);
            $value = $form_data[$field_name_key] ?? ''; // Use $field_name_key which is the sanitized name

            if ($is_required && empty(trim($value))) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => sprintf('Il campo "%s" è obbligatorio.', $field_label)
                ], 400);
            }
        }
    }

    // Sanitize and Prepare Data
    foreach ($form_data as $key => $value) {
        $field_name = sanitize_key($key);
        $field_config = $fields_config_map[$field_name] ?? []; // Get config if available
        $field_type = $field_config['type'] ?? 'text'; // Default to 'text'
        $field_label_for_email = esc_html($field_config['label'] ?? $field_name);


        $sanitized_value = '';
        switch ($field_type) {
            case 'email':
                $sanitized_value = sanitize_email($value);
                break;
            case 'textarea':
                $sanitized_value = sanitize_textarea_field($value);
                break;
            case 'text':
            case 'select': // select values are also text
            default: // any other type not explicitly handled
                $sanitized_value = sanitize_text_field($value);
                break;
        }
        $sanitized_data[$field_name] = $sanitized_value;
        $email_body_parts[] = $field_label_for_email . ": " . $sanitized_value;
    }

    // Prepare for DB Insertion
    // Map internal field names (from form data keys) to specific DB columns
    // The keys of $primary_db_columns_map are the expected field names in $sanitized_data
    $primary_db_columns_map = [
        'name' => 'name',       // if a field is named 'name', it goes to 'name' DB column
        'email' => 'email',     // if a field is named 'email', it goes to 'email' DB column
        'message' => 'message', // if a field is named 'message', it goes to 'message' DB column
        'tel' => 'tel',         // if a field is named 'tel', it goes to 'tel' DB column
        'info' => 'info',       // if a field is named 'info', it goes to 'info' DB column
        // Add more direct mappings if other specific DB columns exist (e.g. 'telefono' => 'tel')
    ];
    $other_db_fields = [];

    foreach ($sanitized_data as $s_key => $s_value) {
        if (array_key_exists($s_key, $primary_db_columns_map)) {
            $db_column_name = $primary_db_columns_map[$s_key];
            $db_data[$db_column_name] = $s_value;
        } else {
            // Store in a generic way if not a primary field
            // The label from config map might be useful here for context if needed
            $field_label = $fields_config_map[$s_key]['label'] ?? $s_key;
            $other_db_fields[esc_html($field_label)] = $s_value; // Use label as key for other_data
        }
    }

    if (!empty($other_db_fields)) {
        // Assuming an 'other_data' TEXT column exists or will be added.
        // If it doesn't exist, $wpdb->insert will ignore this part of the $db_data array.
        $db_data['other_data'] = wp_json_encode($other_db_fields, JSON_UNESCAPED_UNICODE);
    }

    // Fallback for essential fields if not directly mapped but exist with common names
    // This is to maintain some backward compatibility if 'name', 'email', 'message' are field names
    // but not explicitly configured via _fieldsConfig to map to the main columns.
    if (empty($db_data['name']) && isset($sanitized_data['name'])) $db_data['name'] = $sanitized_data['name'];
    if (empty($db_data['email']) && isset($sanitized_data['email'])) $db_data['email'] = $sanitized_data['email'];
    if (empty($db_data['message']) && isset($sanitized_data['message'])) $db_data['message'] = $sanitized_data['message'];


    // Ensure essential fields for the DB table are at least empty strings if not provided
    // to prevent DB errors if columns are NOT NULL without defaults.
    // Adjust based on actual DB schema. The fsdb_forms table has NULL allowed for most.
    $db_data['name'] = $db_data['name'] ?? ($sanitized_data['name'] ?? ''); // Prioritize mapped, then direct, then empty
    $db_data['email'] = $db_data['email'] ?? ($sanitized_data['email'] ?? '');
    $db_data['message'] = $db_data['message'] ?? ($sanitized_data['message'] ?? '');
    $db_data['tel'] = $db_data['tel'] ?? ($sanitized_data['tel'] ?? '');
    $db_data['info'] = $db_data['info'] ?? ($sanitized_data['info'] ?? '');


    // Basic check for at least one piece of identifying information before saving & emailing
    // This is a simplified check. More robust logic might be needed based on specific requirements.
    if (empty(trim(implode('', array_intersect_key($sanitized_data, array_flip(['name', 'email', 'message', 'tel', 'info']))))) && empty(trim(implode('', $other_db_fields)))) {
         // If all primary fields and other fields are essentially empty, it might be a spam or empty submission
        if (empty($fields_config_map)) { // If there was no config, we can't rely on isRequired checks
             return new WP_REST_Response(['success' => false, 'message' => 'Per favore, compila almeno un campo.'], 400);
        }
    }


    global $wpdb;
    $table_name = $wpdb->prefix . 'fsdb_forms';
    $result = $wpdb->insert($table_name, $db_data);

    if ($result === false) {
        // Log error: $wpdb->last_error
        error_log("FSDB DB Insert Error: " . $wpdb->last_error);
        return new WP_REST_Response(['success' => false, 'message' => 'Errore nel salvataggio dei dati.'], 500);
    }

    // Admin Email
    $admin_email = get_option('admin_email');

    // Try to find a name for the email subject, default to 'Modulo Contatto'
    $subject_name_field = $db_data['name'] ?? $sanitized_data['name'] ?? $sanitized_data['nome'] ?? 'Modulo Contatto';
    if(empty(trim($subject_name_field)) || $subject_name_field === 'Modulo Contatto'){
        // try to get email if name is not available for subject
        $subject_name_field = $db_data['email'] ?? $sanitized_data['email'] ?? 'Modulo Contatto';
    }


    $subject = sprintf('Nuova richiesta da %s', esc_html($subject_name_field));
    $body = implode("\n", $email_body_parts); // email_body_parts already contains sanitized, HTML-escaped labels

    wp_mail($admin_email, $subject, $body);

    return new WP_REST_Response(['success' => true, 'message' => 'Richiesta inviata con successo.']);
}

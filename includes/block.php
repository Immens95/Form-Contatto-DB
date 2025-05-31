<?php
add_action('init', 'fsdb_register_block');

function fsdb_register_block() {
    wp_register_script('fsdb-block', FSDB_URL . 'js/form-block.js', ['wp-blocks', 'wp-element', 'wp-editor', 'wp-block-editor'], '1.0', true);
    register_block_type('fsdb/form', [
        'editor_script' => 'fsdb-block',
        'render_callback' => 'fsdb_render_form_block',
        'attributes' => [],
          'supports' => [
            'color' => [
              'gradients' => true,
              'background' => true,
              'text' => true,
              'button' => true,
            ],
            'spacing' => [
              'margin' => true,
              'padding' => true
            ],
            'typography' => [
              'fontSize' => true,
              'lineHeight' => true,
              'textAlign' => true,
              'fontWeight' => true,
              'fontFamily' => true
            ],
            'align' => true,
            'alignWide' => true,
            'background' => [
              'backgroundImage' => true,
              'backgroundSize' => true
            ],
            'border' => [
              'color' => true,
              'width' => true,
              'radius' => true,
              'style' => true
            ],
            'position' => [
              'sticky' => true
            ],
            'shadow' => true,
            'customClassName' => true
          ]
    ]);
}

add_action('wp_enqueue_scripts', 'fsdb_enqueue_frontend');

function fsdb_enqueue_frontend() {
    wp_enqueue_script('fsdb-submit', FSDB_URL . 'js/form-submit.js', ['jquery'], '1.0', true);
    wp_enqueue_style('fsdb-style', FSDB_URL . 'style.css');
    wp_localize_script('fsdb-submit', 'fsdb_ajax', [
        'url' => rest_url('fsdb/v1/send')
    ]);
}


add_action('admin_enqueue_scripts', 'fsdb_enqueue_backend');

function fsdb_enqueue_backend() {
    wp_enqueue_style('fsdb-style', FSDB_URL . 'style.css');
}

function fsdb_render_form_block($attributes) {
    if (is_admin()) {
        return ''; // In editor, the js/form-block.js handles the preview
    }

    $fields = $attributes['fields'] ?? [];
    $buttonText = esc_html($attributes['buttonText'] ?? 'Invia');
    $columns = esc_attr($attributes['columns'] ?? '1');
    $inputSize = esc_attr($attributes['inputSize'] ?? 'medium');
    $textAlign = esc_attr($attributes['textAlign'] ?? 'left'); // This will be applied to the outer div

    $form_html = sprintf('<div style="text-align:%s;">', $textAlign);
    $form_html .= sprintf(
        '<form class="fsdb-form form-columns-%s input-size-%s" id="fsdb-form">',
        $columns,
        $inputSize
    );

    foreach ($fields as $field) {
        $label = esc_html($field['label'] ?? '');
        $name = esc_attr($field['name'] ?? '');
        $type = esc_attr($field['type'] ?? 'text');
        $placeholder = esc_attr($field['placeholder'] ?? '');
        $is_required = !empty($field['isRequired']);
        $required_attr = $is_required ? 'required' : '';
        $id = 'fsdb-field-' . $name . '-' . rand(1000,9999); // Add random part to ID for more uniqueness if form is on page multiple times

        $form_html .= '<p>'; // Each field is wrapped in a paragraph

        // Print the main label for types that need it separately above the input
        // For checkbox and radio, the label is integrated differently.
        if ($type !== 'checkbox' && $type !== 'radio') {
            $form_html .= sprintf(
                '<label for="%s">%s%s</label><br>',
                esc_attr($id),
                $label, // $label is already esc_html'd
                $is_required ? ' <span class="fsdb-required">*</span>' : ''
            );
        }

        switch ($type) {
            case 'text':
            case 'email':
                $form_html .= sprintf(
                    '<input type="%s" id="%s" name="%s" placeholder="%s" %s>',
                    $type,
                    esc_attr($id),
                    $name,
                    $placeholder,
                    $required_attr
                );
                break;
            case 'textarea':
                $form_html .= sprintf(
                    '<textarea id="%s" name="%s" placeholder="%s" %s></textarea>',
                    esc_attr($id),
                    $name,
                    $placeholder,
                    $required_attr
                );
                break;
            case 'select':
                $form_html .= sprintf('<select id="%s" name="%s" %s>', esc_attr($id), $name, $required_attr);
                $form_html .= '<option value="">Seleziona...</option>';
                $options_str = $field['options'] ?? '';
                if (!empty($options_str)) {
                    $options = array_map('trim', explode(',', $options_str));
                    foreach ($options as $opt) {
                        if (empty($opt)) continue; // Skip empty options that might result from ",,,"
                        $form_html .= sprintf(
                            '<option value="%s">%s</option>',
                            esc_attr($opt),
                            esc_html($opt)
                        );
                    }
                }
                $form_html .= '</select>';
                break;
            case 'checkbox':
                // Label wraps input and text for checkboxes
                $form_html .= sprintf(
                    '<label for="%s">',
                    esc_attr($id)
                );
                $form_html .= sprintf(
                    '<input type="checkbox" id="%s" name="%s" value="true" %s> ', // Space after input
                    esc_attr($id),
                    $name, // $name is already esc_attr'd
                    $required_attr // $required_attr is already prepared
                );
                $form_html .= $label; // $label is already esc_html'd
                // Required span directly after the label text for checkbox
                $form_html .= ($is_required ? ' <span class="fsdb-required">*</span>' : '');
                $form_html .= '</label>';
                break;
            case 'radio':
                $form_html .= '<fieldset style="border: none; padding: 0; margin: 0;">'; // Use fieldset for grouping
                $form_html .= sprintf(
                    '<legend style="padding: 0; margin-bottom: 5px; font-weight: bold;">%s%s</legend>', // Style legend like a label
                    $label, // $label is already esc_html'd
                    ($is_required ? ' <span class="fsdb-required">*</span>' : '')
                );
                $options_str = $field['options'] ?? '';
                if (!empty($options_str)) {
                    $radio_options = array_map('trim', explode(',', $options_str));
                    foreach ($radio_options as $index => $opt) {
                        if (empty($opt)) continue;
                        $option_id = $id . '-' . $index;
                        $form_html .= sprintf(
                            '<label for="%s" style="display: block; margin-bottom: 5px; font-weight: normal;">', // Each radio on new line
                            esc_attr($option_id)
                        );
                        $form_html .= sprintf(
                            '<input type="radio" id="%s" name="%s" value="%s" %s> ',
                            esc_attr($option_id),
                            $name, // $name is already esc_attr'd
                            esc_attr($opt),
                            $required_attr // Apply required to all for robustness, browser handles group
                        );
                        $form_html .= esc_html($opt);
                        $form_html .= '</label>';
                    }
                }
                $form_html .= '</fieldset>';
                break;
        }
        $form_html .= '</p>';
    }

    // Button alignment will be primarily handled by the outer div's text-align style.
    // The fsdb-button class can be used for further specific styling if needed.
    $form_html .= sprintf(
        '<p class="fsdb-button"><button type="submit">%s</button></p>',
        $buttonText
    );
    $form_html .= '<div id="fsdb-response"></div>';
    $form_html .= '</form>';
    $form_html .= '</div>'; // Close text-align wrapper

    return $form_html;
}

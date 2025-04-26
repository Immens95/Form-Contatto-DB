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
    $nome = sanitize_text_field($data['nome'] ?? '');
    $email = sanitize_email($data['email'] ?? '');
    $tel = sanitize_text_field($data['tel'] ?? '');
    $info = sanitize_text_field($data['info'] ?? '');
    $messaggio = sanitize_textarea_field($data['messaggio'] ?? '');

    if (!$nome || !$email || !$messaggio) {
        return new WP_REST_Response(['success' => false, 'message' => 'Compila tutti i campi.'], 400);
    }

    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'fsdb_forms', array( 'name'=>$nome, 'email'=>$email, 'tel'=>$tel,'info'=>$info, 'message'=>$messaggio ));

    // invio email admin
    $admin_email = get_option('admin_email');
    $subject = "Nuova richiesta da $nome";
    $body = "Nome: $nome\nEmail: $email\Tel: $tel\info: $info\nMessaggio:\n$messaggio";
    wp_mail($admin_email, $subject, $body);

    return new WP_REST_Response(['success' => true, 'message' => 'Richiesta inviata con successo.']);
}

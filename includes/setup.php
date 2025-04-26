<?php
register_activation_hook(__FILE__, 'fsdb_create_table');

function fsdb_create_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'fsdb_forms';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED AUTO_INCREMENT,
        name VARCHAR(255),
        email VARCHAR(255),
        tel VARCHAR(255),
        message TEXT,
        info TEXT,
        date_uploaded datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

fsdb_create_table();



add_action('admin_post_fsdb_export_html_pdf', 'fsdb_export_html_pdf');

function fsdb_export_html_pdf() {
    global $wpdb;

    $per_page = 10;
    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($paged - 1) * $per_page;

    $table_name = $wpdb->prefix . 'fsdb_forms';
    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$table_name} ORDER BY date_uploaded DESC LIMIT %d OFFSET %d", $per_page, $offset),
        ARRAY_A
    );

    echo '<html><head><title>Esporta Richieste</title>';
    echo '<style>
        body { font-family: sans-serif; margin: 20px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; font-size: 14px; }
        th { background-color: #eee; }
    </style>';
    echo '<script>window.onload = function() { window.print(); }</script>';
    echo '</head><body>';
    echo '<h2>Richieste Ricevute - Pagina ' . $paged . '</h2>';
    echo '<table><thead><tr>';

    if (!empty($results)) {
        foreach (array_keys($results[0]) as $header) {
            echo '<th>' . esc_html($header) . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . esc_html($cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody>';
    } else {
        echo '<tr><td colspan="100%">Nessun dato disponibile</td></tr>';
    }

    echo '</table>';
    echo '</body></html>';
    exit;
}

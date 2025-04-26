<?php
add_action('admin_menu', function () {
    add_menu_page('Richieste Contatto', 'Richieste Contatto', 'manage_options', 'fsdb_admin', 'fsdb_admin_page', 'dashicons-email-alt2');
});

function fsdb_admin_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'fsdb_forms';

    if (isset($_GET['download_csv'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="richieste-contatto.csv"');
        $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY date_uploaded DESC", ARRAY_A);
        $output = fopen("php://output", "w");
        fputcsv($output, array_keys($rows[0]));
        foreach ($rows as $row) fputcsv($output, $row);
        fclose($output);
        exit;
    }


$current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

    $results = $wpdb->get_results("SELECT * FROM $table ORDER BY date_uploaded DESC");

    echo "<div class='wrap'><h1>Richieste Contatto</h1>";
    echo "<a href='?page=fsdb_admin&download_csv=1' class='button'>Esporta CSV</a>";
    echo "<a href=". admin_url('admin-post.php?action=fsdb_export_html_pdf&paged=' . $current_page).' target="_blank" class="button">Esporta PDF</a>';
    echo '<div class="filters">Filters</div>';
    echo "<table class='widefat'>";
        echo "<thead><tr>";
            echo "<th>Data</th>";
            echo "<th>Nome</th>";
            echo "<th>Email</th>";
            echo "<th>Telefono</th>";
            echo "<th>Info</th>";
            echo "<th>Messaggio</th>";
        echo "</tr></thead><tbody>";

    foreach ($results as $r) {
        echo "<tr><td>$r->date_uploaded</td><td>$r->name</td><td>$r->email</td><td>$r->tel</td><td>$r->info</td><td>$r->message</td></tr>";
    }

    echo "</tbody></table></div>";
}

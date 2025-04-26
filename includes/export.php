<?php
use Dompdf\Dompdf;
use Dompdf\Options;

add_action('admin_post_fsdb_export_pdf', 'fsdb_export_pdf');

function fsdb_export_pdf() {
    require_once plugin_dir_path(__FILE__) . '../libs/dompdf/autoload.inc.php';

    global $wpdb;

    $per_page = 10;
    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($paged - 1) * $per_page;

    $table_name = $wpdb->prefix . 'fsdb_richieste';
    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$table_name} ORDER BY data DESC LIMIT %d OFFSET %d", $per_page, $offset),
        ARRAY_A
    );

    $html = '<h2>Richieste - Pagina ' . $paged . '</h2>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0">';
    $html .= '<thead><tr>';
    if (!empty($results)) {
        foreach (array_keys($results[0]) as $header) {
            $html .= '<th>' . esc_html($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($results as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . esc_html($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
    } else {
        $html .= '<tr><td colspan="100%">Nessun dato disponibile</td></tr>';
    }
    $html .= '</table>';

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $dompdf->stream('richieste-pagina-' . $paged . '.pdf', ["Attachment" => true]);
    exit;
}

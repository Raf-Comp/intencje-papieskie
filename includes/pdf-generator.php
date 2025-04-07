<?php
namespace IntencjePapieskie;

if (!defined('ABSPATH')) exit;

use Mpdf\Mpdf;

/**
 * 🖨️ Generator PDF intencji papieskich
 * Obsługuje URL z parametrem ?generate_intencje_pdf=ROK
 * Generuje dwustronicowy dokument z intencjami
 */
class IP_PDF_Generator {

    public function __construct() {
        add_action('init', [$this, 'handle_pdf_request']);
        add_shortcode('intencje_papieskie_pdf', [$this, 'render_pdf_button']);
    }

    /**
     * 📥 Obsługa żądania wygenerowania PDF
     */
    public function handle_pdf_request() {
        if (!isset($_GET['generate_intencje_pdf']) || !current_user_can('manage_options')) {
            return;
        }

        require_once __DIR__ . '/../vendor/autoload.php';
        global $wpdb;

        $rok = intval($_GET['generate_intencje_pdf']);
        $tabela = $wpdb->prefix . 'intencje_papieskie';

        $wyniki = $wpdb->get_results($wpdb->prepare(
            "SELECT miesiac, intencja_powszechna FROM $tabela WHERE rok = %d ORDER BY FIELD(miesiac,
            'Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec',
            'Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień')", $rok
        ));

        if (!$wyniki) {
            wp_die('Brak intencji na wybrany rok.');
        }

        $miesiace = array_map(function($w) {
            return ['miesiac' => $w->miesiac, 'tresc' => $w->intencja_powszechna];
        }, $wyniki);

        $lewa1 = array_slice($miesiace, 0, 6);
        $lewa2 = array_slice($miesiace, 6, 6);

        $html = "<html><body style='font-family: \"Times New Roman\", serif;'>";

        $html .= '<div style="display:flex; justify-content:space-between; gap:20px;">';
        $html .= '<div style="width:49%;">' . $this->render_table($lewa1) . '</div>';
        $html .= '<div style="width:49%;">' . $this->render_table($lewa1) . '</div>';
        $html .= '</div><pagebreak />';

        $html .= '<div style="display:flex; justify-content:space-between; gap:20px;">';
        $html .= '<div style="width:49%;">' . $this->render_table($lewa2) . '</div>';
        $html .= '<div style="width:49%;">' . $this->render_table($lewa2) . '</div>';
        $html .= '</div></body></html>';

        $mpdf = new Mpdf(['default_font' => 'dejavuserif']);
        $mpdf->WriteHTML($html);
        $mpdf->Output("intencje-papieskie-$rok.pdf", "D");
        exit;
    }

    /**
     * 🧾 Renderuje jedną tabelę miesięcy do PDF
     *
     * @param array $dane Lista [miesiac, tresc]
     * @return string HTML
     */
    private function render_table($dane) {
        $html = '<table style="width:100%; font-family: \'Times New Roman\', serif; font-size:12pt;">';
        $html .= '<tr><td style="text-align:center; font-weight:bold; font-size:14pt;" colspan="2">Intencje papieskie</td></tr>';

        foreach ($dane as $m) {
            $html .= '<tr><td colspan="2" style="text-align:center; font-weight:bold; padding-top:10px">' . esc_html($m['miesiac']) . '</td></tr>';
            $html .= '<tr><td colspan="2" style="text-align:justify; padding-bottom:5px">' . esc_html($m['tresc']) . '</td></tr>';
        }

        $html .= '</table>';
        return $html;
    }

    /**
     * 🖱️ Shortcode [intencje_papieskie_pdf] – generuje przycisk PDF dla danego roku
     *
     * @param array $atts
     * @return string
     */
    public function render_pdf_button($atts) {
        $rok = isset($atts['rok']) ? intval($atts['rok']) : date('Y');
        $url = add_query_arg(['generate_intencje_pdf' => $rok], home_url());

        return '<div style="margin: 30px 0; text-align: center;">
            <a href="' . esc_url($url) . '" target="_blank"
               style="display: inline-block; padding: 12px 24px; background-color: #2271b1; color: white;
               text-decoration: none; border-radius: 5px; font-size: 16px;">
               📄 Pobierz intencje papieskie jako PDF ('.$rok.')
            </a>
        </div>';
    }
}

new IP_PDF_Generator();

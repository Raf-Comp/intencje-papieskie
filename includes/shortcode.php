<?php
if (!defined('ABSPATH')) exit;

use IntencjePapieskie\License_Manager;

/**
 * 📜 Klasa obsługująca shortcode [intencje_papieskie]
 */
class IP_Shortcode {

    public function __construct() {
        add_shortcode('intencje_papieskie', [$this, 'render']);
    }

    /**
     * 🔐 Sprawdza, czy licencja z Centrum Licencji jest aktywna.
     *
     * @return bool
     */
    private function is_activated() {
        return function_exists('\\IntencjePapieskie\\is_license_active') && \IntencjePapieskie\is_license_active();
    }

    /**
     * 🎨 Renderuje shortcode [intencje_papieskie]
     *
     * @param array $atts Atrybuty shortcode (np. ['rok' => 2025])
     * @return string HTML z intencjami papieskimi lub komunikatem
     */
    public function render($atts) {
        if (!$this->is_activated()) {
            return '<div style="padding:20px;border:2px dashed red;color:red;font-weight:bold;text-align:center;">
                🔒 Ta wtyczka nie została aktywowana dla tej domeny. <br>
                Prosimy o kontakt: <a href="https://raf-comp.net.pl" target="_blank">raf-comp.net.pl</a>
            </div>';
        }

        global $wpdb;
        $a = shortcode_atts(['rok' => date('Y')], $atts);
        $rok = intval($a['rok']);

        $miesiace = [
            'Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec',
            'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'
        ];

        $tabela = $wpdb->prefix . 'intencje_papieskie';
        $wyniki = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $tabela WHERE rok = %d AND (status = 0 OR status IS NULL)",
            $rok
        ));

        if (!$wyniki) return "<p>Brak aktywnych intencji na rok {$rok}.</p>";

        $out = "<h3>🗓️ Intencje papieskie na rok {$rok}</h3>";
        $out .= "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";

        foreach ($miesiace as $m) {
            foreach ($wyniki as $i) {
                if ($i->miesiac === $m) {
                    $out .= "<div><h4>$m</h4><strong>Intencja:</strong> {$i->intencja_powszechna}</div>";
                    break;
                }
            }
        }

        $out .= "</div>";

        $out .= "<div style='text-align:center; margin-top: 40px;'>
            <a href='/archiwum-intencji-papieskich' class='button'
               style='padding: 10px 20px; font-size: 16px; background: #2271b1; color: white; border-radius: 4px; text-decoration: none;'>
                Archiwum intencji papieskich
            </a>
        </div>";

        // Dodanie przycisku PDF
        $pdf_url = add_query_arg(['generate_intencje_pdf' => $rok], home_url());
        $out .= "<div style='text-align:center; margin-top: 30px;'>
            <a href='" . esc_url($pdf_url) . "' target='_blank'
               style='display: inline-block; padding: 12px 24px; background-color: #2271b1; color: white;
               text-decoration: none; border-radius: 5px; font-size: 16px;'>
               📄 Pobierz intencje jako PDF ({$rok})
            </a>
        </div>";

        return $out;
    }
}

// ✅ Inicjalizacja shortcode tylko gdy funkcja licencji istnieje
if (function_exists('\\IntencjePapieskie\\is_license_active')) {
    new IP_Shortcode();
}

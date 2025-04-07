<?php
if (!defined('ABSPATH')) exit;

use IntencjePapieskie\License_Manager;

/**
 * 🗂️ Klasa obsługująca shortcode [intencje_papieskie_archiwum]
 */
class IP_Shortcode_Archiwum {

    public function __construct() {
        add_shortcode('intencje_papieskie_archiwum', [$this, 'render']);
    }

    /**
     * 🔍 Renderuje archiwalne intencje papieskie na podstawie wybranego roku
     *
     * @param array $atts Atrybuty shortcode
     * @return string HTML z listą intencji
     */
    public function render($atts) {
        if (!function_exists('\IntencjePapieskie\is_license_active') || !\IntencjePapieskie\is_license_active()) {
            return '<div style="padding:20px;border:2px dashed red;color:red;font-weight:bold;text-align:center;">
                🔒 Wtyczka nie jest aktywna – brak ważnej licencji.
            </div>';
        }

        global $wpdb;

        $tabela = $wpdb->prefix . 'intencje_papieskie';
        $lata = $wpdb->get_col("SELECT DISTINCT rok FROM $tabela ORDER BY rok DESC");

        $rok = isset($_GET['rok']) ? intval($_GET['rok']) : (int)date('Y') - 1;

        $wyniki = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tabela WHERE rok = %d", $rok));

        if (!$wyniki) return "<p>Brak intencji dla roku {$rok}.</p>";

        ob_start();
        ?>
        <div class="intencje-archiwum">
            <h3>📁 Archiwum intencji papieskich – rok <?php echo esc_html($rok); ?></h3>
            <form method="get" style="margin-bottom: 20px;">
                <?php foreach ($_GET as $key => $value): if ($key !== 'rok') : ?>
                    <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                <?php endif; endforeach; ?>
                <label for="rok">Wybierz rok:</label>
                <select name="rok" id="rok" onchange="this.form.submit()">
                    <?php foreach ($lata as $r): ?>
                        <option value="<?php echo esc_attr($r); ?>" <?php selected($rok, $r); ?>><?php echo esc_html($r); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <ul style="line-height:1.8em;">
                <?php foreach ($wyniki as $i): ?>
                    <li><strong><?php echo esc_html($i->miesiac); ?>:</strong> <?php echo esc_html($i->intencja_powszechna); ?></li>
                <?php endforeach; ?>
            </ul>

            <div style="text-align:center; margin-top:30px;">
                <a href="/intencje-papieskie" class="button" style="padding:10px 20px; font-size:16px; background:#444; color:white; text-decoration:none; border-radius:4px;">
                    ⬅️ Powrót do aktualnych intencji
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// ✅ Inicjalizacja tylko przy aktywnej licencji
if (function_exists('\IntencjePapieskie\is_license_active') && \IntencjePapieskie\is_license_active()) {
    new IP_Shortcode_Archiwum();
}

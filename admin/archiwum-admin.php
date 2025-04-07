<?php
/**
 * 🗂️ Widok archiwum intencji papieskich w kokpicie admina
 */

if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_options')) {
    wp_die('Brak dostępu');
}

/**
 * 👤 Klasa IP_Archiwum_Admin – wyświetla listę intencji z wybranego roku
 */
class IP_Archiwum_Admin {

    /**
     * 🖥️ Renderuje formularz i listę intencji archiwalnych
     */
    public function render() {
        global $wpdb;
        $table = $wpdb->prefix . 'intencje_papieskie';

        // Pobierz unikalne lata
        $lata = $wpdb->get_col("SELECT DISTINCT rok FROM $table ORDER BY rok DESC");

        ?>
        <div class="wrap">
            <h1>🗂️ Archiwum Intencji Papieskich</h1>
            <form method="get" style="margin-bottom: 20px;">
                <input type="hidden" name="page" value="intencje-papieskie-archiwum" />
                <label for="rok">Wybierz rok:</label>
                <select name="rok" id="rok">
                    <?php foreach ($lata as $rok): ?>
                        <option value="<?php echo esc_attr($rok); ?>" <?php selected($_GET['rok'] ?? '', $rok); ?>><?php echo esc_html($rok); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button">Pokaż</button>
            </form>

            <?php
            if (!empty($_GET['rok'])) {
                $rok = intval($_GET['rok']);
                $wyniki = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE rok = %d", $rok));

                if ($wyniki) {
                    echo "<h2>Intencje papieskie na rok $rok</h2>";
                    echo "<ul style='line-height:1.8em;'>";
                    foreach ($wyniki as $i) {
                        echo "<li><strong>" . esc_html($i->miesiac) . ":</strong> " . esc_html($i->intencja_powszechna) . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>Brak intencji na wybrany rok.</p>";
                }
            }
            ?>
        </div>
        <?php
    }
}

// Renderuj tylko jeśli jesteśmy w kokpicie
if (is_admin()) {
    $page = new IP_Archiwum_Admin();
    $page->render();
}

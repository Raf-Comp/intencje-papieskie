<?php
/**
 * 🖊️ Panel edycji istniejących intencji papieskich
 */

if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_options')) {
    wp_die('Brak uprawnień do edycji intencji.');
}

/**
 * ✏️ Klasa IP_Edit_Intencje – pozwala edytować wpisy intencji
 */
class IP_Edit_Intencje {

    /**
     * 📋 Renderuje formularz i obsługuje zapis zmian
     */
    public function render() {
        global $wpdb;
        $table = $wpdb->prefix . 'intencje_papieskie';

        // Obsługa zapisu edycji
        if (isset($_POST['zapisz'], $_POST['id'], $_POST['_wpnonce']) &&
            wp_verify_nonce($_POST['_wpnonce'], 'zapisz_intencje')) {

            $id = intval($_POST['id']);
            $intencja = sanitize_text_field($_POST['intencja_powszechna']);

            $wpdb->update($table, ['intencja_powszechna' => $intencja], ['id' => $id]);

            echo '<div class="notice notice-success"><p>✅ Intencja została zaktualizowana.</p></div>';
        }

        // Pobierz wszystkie intencje
        $wyniki = $wpdb->get_results("SELECT * FROM $table ORDER BY rok DESC, FIELD(miesiac,
            'Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec',
            'Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień')");

        ?>
        <div class="wrap">
            <h1>🖊️ Edytuj Intencje Papieskie</h1>
            <table class="widefat striped">
                <thead>
                    <tr><th>Rok</th><th>Miesiąc</th><th>Intencja</th><th>Akcja</th></tr>
                </thead>
                <tbody>
                <?php foreach ($wyniki as $w): ?>
                    <tr>
                        <form method="post">
                            <?php wp_nonce_field('zapisz_intencje'); ?>
                            <td><?php echo esc_html($w->rok); ?></td>
                            <td><?php echo esc_html($w->miesiac); ?></td>
                            <td>
                                <input type="text" name="intencja_powszechna" value="<?php echo esc_attr($w->intencja_powszechna); ?>" class="regular-text" />
                            </td>
                            <td>
                                <input type="hidden" name="id" value="<?php echo intval($w->id); ?>">
                                <input type="submit" name="zapisz" class="button button-primary" value="Zapisz">
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

// Uruchomienie widoku
$page = new IP_Edit_Intencje();
$page->render();

<?php
/**
 * 📥 Formularz importu intencji papieskich z pliku CSV
 */

if (!defined('ABSPATH')) exit;

/**
 * 📤 Klasa IP_Import_Form – obsługuje widok i import pliku CSV
 */
class IP_Import_Form {

    /**
     * 🖥️ Renderuje formularz i obsługuje import
     */
    public function render() {
        if (!current_user_can('manage_options')) wp_die('Brak dostępu');

        global $wpdb;
        $table = $wpdb->prefix . 'intencje_papieskie';

        if (isset($_POST['submit_csv'], $_FILES['csv_file']) && !empty($_FILES['csv_file']['tmp_name']) &&
            check_admin_referer('ip_import_csv_nonce')) {

            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
            $row = 0;

            while (($line = fgetcsv($file, 1000, ';')) !== FALSE) {
                $row++;
                if ($row === 1) continue; // pomiń nagłówek

                $rok = isset($line[0]) ? intval(trim($line[0])) : 0;
                $miesiac = isset($line[1]) ? trim($line[1]) : '';
                $intencja = isset($line[2]) ? trim($line[2]) : '';

                if ($rok > 2000 && !empty($miesiac) && !empty($intencja)) {
                    $wpdb->insert($table, [
                        'rok' => $rok,
                        'miesiac' => $miesiac,
                        'intencja_powszechna' => sanitize_text_field($intencja),
                        'status' => 0
                    ]);
                }
            }

            fclose($file);
            echo '<div class="notice notice-success"><p>✅ Intencje zostały zaimportowane z pliku CSV.</p></div>';
        }

        $this->render_form();
    }

    /**
     * 📄 Wyświetla formularz HTML
     */
    private function render_form() {
        ?>
        <div class="wrap">
            <h1>📥 Import intencji papieskich z pliku CSV</h1>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('ip_import_csv_nonce'); ?>
                <input type="file" name="csv_file" accept=".csv" required>
                <input type="submit" name="submit_csv" class="button button-primary" value="Importuj CSV">
            </form>
            <p style="margin-top:20px;"><strong>Format pliku CSV:</strong><br>
            Plik powinien zawierać dane w formacie z nagłówkiem:<br>
            <code>rok;miesiac;intencja_powszechna</code><br>
            Przykład:<br>
            <code>
                2025;Styczeń;Modlitwa o pokój<br>
                2025;Luty;Pomoc ubogim<br>
            </code></p>
        </div>
        <?php
    }
}

// Inicjalizacja klasy i renderowanie widoku
$form = new IP_Import_Form();
$form->render();

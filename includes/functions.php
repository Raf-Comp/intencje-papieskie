<?php
if (!defined('ABSPATH')) exit;

/**
 * ⚙️ Klasa odpowiedzialna za funkcje techniczne:
 * - Tworzenie tabeli z intencjami
 * - Automatyczne archiwizowanie zakończonych intencji
 */
class IP_Functions {

    public function __construct() {
        add_action('init', [$this, 'auto_archive']);
    }

    /**
     * 🔨 Tworzy tabelę intencji papieskich w bazie danych
     */
    public static function create_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'intencje_papieskie';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            rok int(4) NOT NULL,
            miesiac varchar(20) NOT NULL,
            intencja_powszechna text NOT NULL,
            status tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * ⏳ Automatycznie archiwizuje intencje sprzed bieżącego miesiąca
     */
    public function auto_archive() {
        if (!is_admin()) return;

        global $wpdb;
        $table = $wpdb->prefix . 'intencje_papieskie';

        $miesiace = [
            'Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec',
            'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'
        ];

        $miesiac_teraz = intval(date('n'));
        $rok_teraz = intval(date('Y'));

        foreach ($miesiace as $index => $miesiac) {
            if ($index + 1 < $miesiac_teraz) {
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE $table SET status = 1 WHERE rok = %d AND miesiac = %s AND status = 0",
                        $rok_teraz, $miesiac
                    )
                );
            }
        }
    }
}

// Inicjalizacja funkcji systemowych
new IP_Functions();

/**
 * 🔐 Inicjalizacja klienta licencji z Centrum Licencji
 */
require_once plugin_dir_path(__FILE__) . 'class-license-client.php';

/**
 * 🔑 Zwraca klienta licencji Centrum Licencji
 *
 * @return \CentrumLicencji_Client|null
 */
function ip_get_license_client() {
    global $wpdb;
    $table = $wpdb->prefix . 'ip_licencja';
    $data = $wpdb->get_row("SELECT * FROM {$table} LIMIT 1");

    if (!$data || empty($data->license_key) || empty($data->api_key)) {
        return null;
    }

    return new \CentrumLicencji_Client(
        $data->license_key,
        home_url(),
        $data->api_key
    );
}

/**
 * 📅 Cotygodniowa weryfikacja licencji przez CRON
 */
add_action('ip_verify_license_weekly', 'ip_verify_license_status');
function ip_verify_license_status() {
    $client = ip_get_license_client();
    if (!$client) {
        return;
    }

    $result = $client->verify();

    global $wpdb;
    $table = $wpdb->prefix . 'ip_licencja';
    $wpdb->update($table, [
        'last_check' => current_time('mysql')
    ], ['id' => 1]);

    set_transient('ip_license_status', $result, WEEK_IN_SECONDS);
}

/**
 * ⚠️ Weryfikacja licencji przy wejściu do admina
 * Pokazuje komunikat, jeśli status licencji jest nieprawidłowy
 */
add_action('admin_init', 'ip_check_license_on_admin_load');
function ip_check_license_on_admin_load() {
    $result = get_transient('ip_license_status');

    if (!$result) {
        ip_verify_license_status();
        $result = get_transient('ip_license_status');
    }

    if (!$result || $result['status'] !== 'success') {
        add_action('admin_notices', function() use ($result) {
            $msg = isset($result['message']) ? $result['message'] : 'Nieznany błąd licencji.';
            echo '<div class="notice notice-error"><p>🔒 Wtyczka Intencje Papieskie: ' . esc_html($msg) . '</p></div>';
            echo '<pre style="background:#fff;border:1px solid #ccc;padding:1em;overflow:auto">';
            var_dump($result);
            echo '</pre>';
        });
    }
}

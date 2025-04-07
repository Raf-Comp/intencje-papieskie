<?php
namespace IntencjePapieskie;

class License_Manager {
    const TABLE = 'wp_intencje_licencja';
    const API_URL = 'https://raf-comp.net.pl/wp-json/centrum-licencji/v1';

    /**
     * 🔐 Aktywacja licencji przez API Centrum Licencji
     */
    public static function activate($license_key, $api_key) {
        $response = wp_remote_post(self::API_URL . '/activate', [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => [
                'license_key' => $license_key,
                'domain_url'  => site_url(),
                'api_key'     => $api_key,
            ]
        ]);

        if (is_wp_error($response)) {
            error_log('🔒 Błąd połączenia z API: ' . $response->get_error_message());
            return ['status' => 'error', 'message' => $response->get_error_message()];
        }

        $code = wp_remote_retrieve_response_code($response);
        $body_raw = wp_remote_retrieve_body($response);
        $body = json_decode($body_raw, true);

        if ($code !== 200 || empty($body) || !isset($body['status']) || $body['status'] !== 'success') {
            error_log("❌ Aktywacja nieudana ({$code}): " . print_r($body, true));
            return ['status' => 'error', 'message' => $body['message'] ?? 'Nieprawidłowa odpowiedź API.'];
        }

        global $wpdb;
        $wpdb->replace(self::TABLE, [
            'license_key'  => $license_key,
            'api_key'      => $api_key,
            'domain_url'   => site_url(),
            'activated_at' => current_time('mysql'),
            'expiry_date'  => $body['expiry_date'] ?? null,
            'is_active'    => 1
        ]);

        error_log("✅ Licencja aktywowana: {$license_key} ({$body['expiry_date']})");
        return ['status' => 'success', 'message' => 'Licencja została aktywowana.'];
    }

    /**
     * 🕵️‍♂️ Weryfikacja licencji
     */
    public static function verify() {
        global $wpdb;
        $row = $wpdb->get_row("SELECT * FROM " . self::TABLE . " ORDER BY id DESC LIMIT 1", ARRAY_A);
        if (!$row) return false;

        $response = wp_remote_post(self::API_URL . '/verify', [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => [
                'license_key' => $row['license_key'],
                'domain_url'  => site_url(),
                'api_key'     => $row['api_key'],
            ]
        ]);

        if (is_wp_error($response)) {
            error_log('🔍 Błąd weryfikacji licencji: ' . $response->get_error_message());
            return false;
        }

        $body_raw = wp_remote_retrieve_body($response);
        $body = json_decode($body_raw, true);

        if (!is_array($body)) {
            error_log('❌ Odpowiedź API niepoprawna: ' . $body_raw);
            return false;
        }

        error_log('📦 Odpowiedź weryfikacji: ' . print_r($body, true));

        if (!isset($body['status']) || !in_array($body['status'], ['valid', 'success'])) {
            error_log('❌ Status odpowiedzi API niepoprawny: ' . ($body['status'] ?? 'brak'));
            return false;
        }

        return true;
    }

    /**
     * ✅ Sprawdza czy lokalna licencja jest aktywna
     */
    public static function is_active() {
        global $wpdb;
        return $wpdb->get_var("SELECT is_active FROM " . self::TABLE . " ORDER BY id DESC LIMIT 1") == 1;
    }

    /**
     * 📄 Pobiera aktualny wiersz licencji
     */
    public static function get_license_row() {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM " . self::TABLE . " ORDER BY id DESC LIMIT 1", ARRAY_A);
    }
}

// 🔓 Globalna funkcja do sprawdzania licencji w kodzie zewnętrznym
function is_license_active() {
    return License_Manager::is_active();
}

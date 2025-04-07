<?php
/**
 * 📡 Obsługuje aktywację licencji AJAX (WP AJAX API)
 */
use IntencjePapieskie\License_Manager;

add_action('wp_ajax_intencje_activate_license', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Brak uprawnień.']);
    }

    if (!check_ajax_referer('ip_license_action', '_wpnonce', false)) {
        wp_send_json_error(['message' => 'Błąd nonce (zabezpieczenie formularza).']);
    }

    $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
    $api_key     = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';

    if (!$license_key || !$api_key) {
        wp_send_json_error(['message' => 'Uzupełnij oba pola.']);
    }

    $result = License_Manager::activate($license_key, $api_key);

    if ($result['status'] === 'success') {
        wp_send_json_success(['message' => '✅ Licencja aktywowana.']);
    } else {
        wp_send_json_error(['message' => '❌ ' . ($result['message'] ?? 'Błąd aktywacji')]);
    }
});

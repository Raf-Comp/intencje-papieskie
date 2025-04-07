<?php
/**
 * 🔐 Panel aktywacji licencji – AJAX + klasyczny POST + test API
 */

use IntencjePapieskie\License_Manager;

if (!defined('ABSPATH')) exit;
if (!current_user_can('manage_options')) wp_die('Brak dostępu');

global $wpdb;
$table = License_Manager::TABLE;
$row = $wpdb->get_row("SELECT * FROM {$table} ORDER BY id DESC LIMIT 1", ARRAY_A);

$message = '';
$notice_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'ip_license_action')) {
        wp_die('Nieautoryzowane żądanie.');
    }

    if (isset($_POST['license_key'], $_POST['api_key'])) {
        $license_key = sanitize_text_field($_POST['license_key']);
        $api_key     = sanitize_text_field($_POST['api_key']);
        $result = License_Manager::activate($license_key, $api_key);

        $notice_class = $result['status'] === 'success' ? 'notice-success' : 'notice-error';
        $message = $result['message'] ?? 'Nieznana odpowiedź.';
        $row = $wpdb->get_row("SELECT * FROM {$table} ORDER BY id DESC LIMIT 1", ARRAY_A);
    }

    if (isset($_POST['verify_license'])) {
        $response = wp_remote_post(License_Manager::API_URL . '/verify', [
            'body' => [
                'license_key' => $row['license_key'],
                'domain_url'  => site_url(),
                'api_key'     => $row['api_key'],
            ]
        ]);

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        $notice_class = $body['status'] === 'valid' ? 'notice-success' : 'notice-error';
        $message = $body['message'] ?? 'Nieznana odpowiedź z API.';
    }

    if (isset($_POST['deactivate_license'])) {
        $wpdb->update($table, ['is_active' => 0], ['id' => $row['id']]);
        $notice_class = 'notice-info';
        $message = 'Licencja została oznaczona jako nieaktywna.';
        $row = $wpdb->get_row("SELECT * FROM {$table} ORDER BY id DESC LIMIT 1", ARRAY_A);
    }

    if (isset($_POST['test_api_connection'])) {
        $response = wp_remote_post(License_Manager::API_URL . '/ping', [
            'timeout' => 10,
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body'    => ['test' => 'ping']
        ]);

        if (is_wp_error($response)) {
            $notice_class = 'notice-error';
            $message = '❌ Błąd połączenia z API: ' . $response->get_error_message();
            error_log('[PING] Błąd: ' . $response->get_error_message());
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $message = '🔌 API odpowiedziało (kod: ' . $code . '): ' . esc_html($body);
            $notice_class = ($code === 200) ? 'notice-success' : 'notice-error';
            error_log('[PING] Odpowiedź (' . $code . '): ' . $body);
        }
    }
}
?>

<div class="wrap">
    <h1>🔐 Licencja – Intencje Papieskie</h1>

    <?php if (!empty($message)) : ?>
        <div class="notice <?php echo esc_attr($notice_class); ?> is-dismissible"><p><?php echo esc_html($message); ?></p></div>
    <?php endif; ?>

    <?php if (!empty($row)) : ?>
        <h2>📜 Aktualna licencja</h2>
        <table class="widefat striped">
            <tr><th>Klucz</th><td><?php echo esc_html($row['license_key']); ?></td></tr>
            <tr><th>API</th><td><?php echo esc_html($row['api_key']); ?></td></tr>
            <tr><th>Domena</th><td><?php echo esc_html($row['domain_url']); ?></td></tr>
            <tr><th>Aktywowano</th><td><?php echo esc_html($row['activated_at']); ?></td></tr>
            <tr><th>Ważna do</th><td><?php echo esc_html($row['expiry_date'] ?? '—'); ?></td></tr>
            <tr><th>Status</th>
                <td><?php echo intval($row['is_active']) === 1
                    ? '<span style="color:green; font-weight:bold;">Aktywna</span>'
                    : '<span style="color:red; font-weight:bold;">Nieaktywna</span>'; ?></td></tr>
        </table>
        <form method="post" style="margin-top: 1em;">
            <?php wp_nonce_field('ip_license_action'); ?>
            <button type="submit" name="verify_license" class="button">🔍 Zweryfikuj</button>
            <button type="submit" name="deactivate_license" class="button" onclick="return confirm('Dezaktywować?');">❌ Dezaktywuj</button>
        </form>
    <?php endif; ?>

    <h2>⚡ Aktywuj licencję (AJAX)</h2>
    <form id="intencje-activate-license-form" method="post">
        <?php wp_nonce_field('ip_license_action'); ?>
        <table class="form-table">
            <tr>
                <th><label for="license_key_ajax">Klucz Licencji</label></th>
                <td><input type="text" name="license_key" id="license_key_ajax" required class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="api_key_ajax">API Key</label></th>
                <td><input type="text" name="api_key" id="api_key_ajax" required class="regular-text"></td>
            </tr>
        </table>
        <button type="submit" class="button button-primary">🔐 Aktywuj przez AJAX</button>
    </form>

    <h2>🔌 Połączenie z API</h2>
    <form method="post" style="margin-top: 1em;">
        <?php wp_nonce_field('ip_license_action'); ?>
        <button type="submit" name="test_api_connection" class="button">🔌 Przetestuj połączenie z API</button>
    </form>
</div>

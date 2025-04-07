<?php
add_action('intencje_verify_license_event', function() {
    if (function_exists('IntencjePapieskie\License_Manager::verify')) {
        if (!\IntencjePapieskie\License_Manager::verify()) {
            global $wpdb;
            $table = $wpdb->prefix . 'intencje_licencja';
            $wpdb->update($table, ['is_active' => 0], ['is_active' => 1]);
        }
    }
});

// Rejestracja zadania cron przy aktywacji
register_activation_hook(__FILE__, function() {
    if (!wp_next_scheduled('intencje_verify_license_event')) {
        wp_schedule_event(time(), 'weekly', 'intencje_verify_license_event');
    }
});

// Usuwanie cron przy dezaktywacji
register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('intencje_verify_license_event');
});

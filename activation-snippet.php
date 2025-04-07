<?php
register_activation_hook(__FILE__, function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'intencje_licencja';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        license_key VARCHAR(255) NOT NULL,
        api_key VARCHAR(255) NOT NULL,
        domain_url VARCHAR(255) NOT NULL,
        activated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expiry_date DATETIME DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});

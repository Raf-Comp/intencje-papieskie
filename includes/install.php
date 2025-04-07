<?php
namespace IntencjePapieskie;

if (!defined('ABSPATH')) exit;

/**
 * 🛠️ Tworzy tabelę licencji dla wtyczki Intencje Papieskie
 * 
 * Kolumny:
 * - license_key: klucz licencji
 * - api_key: klucz API
 * - domain_url: przypisana domena
 * - is_active: status aktywacji (0/1)
 * - activated_at: data aktywacji
 */
function install_licencja_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'intencje_licencja';
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        license_key VARCHAR(255) NOT NULL,
        api_key VARCHAR(255) NOT NULL,
        domain_url TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 0,
        activated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_check DATETIME NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    dbDelta($sql);
}

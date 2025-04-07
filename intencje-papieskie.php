<?php
/**
 * Plugin Name: Intencje Papieskie
 * Description: Wtyczka do wyświetlania intencji papieskich. Wersja licencjonowana.
 * Version: 1.0.0
 * Author: Rafał Danielewski
 */

namespace IntencjePapieskie;


if (!defined('INTENCJE_PAP_PROTECT')) {
    define('INTENCJE_PAP_PROTECT', true);
}
if (!INTENCJE_PAP_PROTECT) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>Błąd:</strong> Wtyczka Intencje Papieskie została zmodyfikowana w sposób niedozwolony.</p></div>';
    });
    return;
}

define('INTENCJE_PAP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('INTENCJE_PAP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoryzacja licencji
require_once INTENCJE_PAP_PLUGIN_PATH . 'includes/license-manager.php';
if (!\IntencjePapieskie\is_license_active()) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>Uwaga:</strong> Wtyczka Intencje Papieskie jest wyłączona – brak aktywnej licencji.</p></div>';
    });
    return;
}

// Wczytujemy tylko jeśli licencja jest aktywna
require_once INTENCJE_PAP_PLUGIN_PATH . 'includes/functions.php';
require_once INTENCJE_PAP_PLUGIN_PATH . 'includes/shortcode.php';
require_once INTENCJE_PAP_PLUGIN_PATH . 'includes/shortcode_archiwum.php';
require_once INTENCJE_PAP_PLUGIN_PATH . 'includes/pdf-generator.php';
require_once plugin_dir_path(__FILE__) . 'admin/ajax-license-handler.php';


// Aktywacja: tabela + cron
register_activation_hook(__FILE__, function() {
    require_once plugin_dir_path(__FILE__) . 'includes/install.php';
    \IntencjePapieskie\install_licencja_table();

    if (!wp_next_scheduled('intencje_verify_license_event')) {
        wp_schedule_event(time(), 'weekly', 'intencje_verify_license_event');
    }
});

// Dezaktywacja: usuń cron
register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('intencje_verify_license_event');
});

// Harmonogram: weryfikacja licencji
add_action('intencje_verify_license_event', function () {
    \IntencjePapieskie\verify_license_cron();
});

// Menu admina
add_action('admin_menu', function () {
    add_menu_page(
        'Intencje Papieskie',
        'Intencje Papieskie',
        'manage_options',
        'intencje-papieskie',
        function () {
            include INTENCJE_PAP_PLUGIN_PATH . 'admin/license.php';
        },
        'dashicons-admin-site-alt3',
        70
    );

    add_submenu_page('intencje-papieskie', 'Edycja intencji', 'Edycja intencji', 'manage_options', 'edycja-intencji', function () {
        include INTENCJE_PAP_PLUGIN_PATH . 'admin/edit-intencje.php';
    });
    add_submenu_page('intencje-papieskie', 'Import CSV', 'Import CSV', 'manage_options', 'import-csv', function () {
        include INTENCJE_PAP_PLUGIN_PATH . 'admin/import-form.php';
    });
    add_submenu_page('intencje-papieskie', 'Informacja o CSV', 'Informacja o CSV', 'manage_options', 'info-csv', function () {
        include INTENCJE_PAP_PLUGIN_PATH . 'admin/info.php';
    });
});

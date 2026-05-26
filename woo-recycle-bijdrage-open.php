<?php
/**
 * Plugin Name: WooCommerce Recycle Bijdrage OPEN
 * Plugin URI:  https://github.com/erwindek/woo-recycle-bijdrage-open
 * Description: Rapportage van verkochte aantallen en gewichten per productgroep voor aangifte bij Stichting OPEN.
 * Version:     1.0.0
 * Author:      Erwin de Kruijf
 * Text Domain: wrbo
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 */

defined( 'ABSPATH' ) || exit;

define( 'WRBO_VERSION', '1.0.0' );
define( 'WRBO_PLUGIN_FILE', __FILE__ );
define( 'WRBO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WRBO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check WooCommerce is active before loading.
 */
function wrbo_init() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>' .
                 esc_html__( 'WooCommerce Recycle Bijdrage OPEN vereist WooCommerce. Activeer WooCommerce eerst.', 'wrbo' ) .
                 '</p></div>';
        } );
        return;
    }

    require_once WRBO_PLUGIN_DIR . 'includes/class-wrbo-open-categories.php';
    require_once WRBO_PLUGIN_DIR . 'includes/class-wrbo-settings.php';
    require_once WRBO_PLUGIN_DIR . 'includes/class-wrbo-product-fields.php';
    require_once WRBO_PLUGIN_DIR . 'includes/class-wrbo-report.php';
    require_once WRBO_PLUGIN_DIR . 'includes/class-wrbo-import.php';
    require_once WRBO_PLUGIN_DIR . 'includes/class-wrbo-refunds.php';
    require_once WRBO_PLUGIN_DIR . 'includes/class-wrbo-admin.php';

    WRBO_Settings::instance();
    WRBO_Product_Fields::instance();
    WRBO_Refunds::instance();
    WRBO_Admin::instance();
}
add_action( 'plugins_loaded', 'wrbo_init' );

register_activation_hook( __FILE__, 'wrbo_activate' );
function wrbo_activate() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $table   = $wpdb->prefix . 'wrbo_refund_deductions';

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (
        id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        refund_id     BIGINT UNSIGNED NOT NULL,
        order_id      BIGINT UNSIGNED NOT NULL,
        product_id    BIGINT UNSIGNED NOT NULL,
        variation_id  BIGINT UNSIGNED NOT NULL DEFAULT 0,
        qty           INT             NOT NULL DEFAULT 0,
        weight_kg     DECIMAL(10,4)   NOT NULL DEFAULT 0,
        refund_date   DATETIME        NOT NULL,
        deducted      TINYINT(1)      NOT NULL DEFAULT 0,
        deducted_period VARCHAR(20)   NULL,
        PRIMARY KEY (id),
        KEY refund_id (refund_id),
        KEY deducted (deducted)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

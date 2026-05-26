<?php
defined( 'ABSPATH' ) || exit;

class WRBO_Admin {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_post_wrbo_import_csv', [ $this, 'handle_import' ] );
        add_action( 'admin_post_wrbo_generate_report', [ $this, 'handle_report' ] );
    }

    public function register_menu(): void {
        add_menu_page(
            __( 'Recycle Bijdrage OPEN', 'wrbo' ),
            __( 'Recycle Bijdrage', 'wrbo' ),
            'manage_woocommerce',
            'wrbo-report',
            [ $this, 'render_report_page' ],
            'dashicons-recycle',
            58
        );
        add_submenu_page(
            'wrbo-report',
            __( 'Rapportage', 'wrbo' ),
            __( 'Rapportage', 'wrbo' ),
            'manage_woocommerce',
            'wrbo-report',
            [ $this, 'render_report_page' ]
        );
        add_submenu_page(
            'wrbo-report',
            __( 'Instellingen', 'wrbo' ),
            __( 'Instellingen', 'wrbo' ),
            'manage_woocommerce',
            'wrbo-settings',
            [ $this, 'render_settings_page' ]
        );
        add_submenu_page(
            'wrbo-report',
            __( 'Importeer gewichten', 'wrbo' ),
            __( 'Importeer gewichten', 'wrbo' ),
            'manage_woocommerce',
            'wrbo-import',
            [ $this, 'render_import_page' ]
        );
    }

    public function enqueue_assets( string $hook ): void {
        $pages = [ 'toplevel_page_wrbo-report', 'recycle-bijdrage_page_wrbo-settings', 'recycle-bijdrage_page_wrbo-import' ];
        if ( ! in_array( $hook, $pages, true ) ) {
            return;
        }
        wp_enqueue_style( 'wrbo-admin', WRBO_PLUGIN_URL . 'admin/css/admin.css', [], WRBO_VERSION );
        wp_enqueue_script( 'wrbo-admin', WRBO_PLUGIN_URL . 'admin/js/admin.js', [ 'jquery' ], WRBO_VERSION, true );
    }

    public function render_report_page(): void {
        require WRBO_PLUGIN_DIR . 'admin/views/report-page.php';
    }

    public function render_settings_page(): void {
        require WRBO_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    public function render_import_page(): void {
        require WRBO_PLUGIN_DIR . 'admin/views/import-page.php';
    }

    public function handle_import(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Geen toegang.', 'wrbo' ) );
        }
        check_admin_referer( 'wrbo_import_csv' );

        $redirect = admin_url( 'admin.php?page=wrbo-import' );

        if ( empty( $_FILES['wrbo_csv']['tmp_name'] ) ) {
            wp_safe_redirect( add_query_arg( 'wrbo_error', urlencode( __( 'Geen bestand geselecteerd.', 'wrbo' ) ), $redirect ) );
            exit;
        }

        $result = WRBO_Import::process_csv( $_FILES['wrbo_csv']['tmp_name'] );

        wp_safe_redirect( add_query_arg( [
            'wrbo_updated' => $result['updated'],
            'wrbo_skipped' => $result['skipped'],
            'wrbo_errors'  => urlencode( implode( '|', $result['errors'] ) ),
        ], $redirect ) );
        exit;
    }

    public function handle_report(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Geen toegang.', 'wrbo' ) );
        }
        check_admin_referer( 'wrbo_generate_report' );

        $date_from        = isset( $_POST['date_from'] ) ? sanitize_text_field( $_POST['date_from'] ) : '';
        $date_to          = isset( $_POST['date_to'] ) ? sanitize_text_field( $_POST['date_to'] ) : '';
        $apply_deductions = ! empty( $_POST['apply_deductions'] );

        wp_safe_redirect( add_query_arg( [
            'page'             => 'wrbo-report',
            'date_from'        => $date_from,
            'date_to'          => $date_to,
            'apply_deductions' => $apply_deductions ? '1' : '0',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }
}

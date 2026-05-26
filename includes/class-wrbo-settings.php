<?php
defined( 'ABSPATH' ) || exit;

class WRBO_Settings {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function register_settings(): void {
        register_setting( 'wrbo_settings_group', 'wrbo_category_mapping', [
            'type'              => 'array',
            'sanitize_callback' => [ $this, 'sanitize_mapping' ],
            'default'           => [],
        ] );
        register_setting( 'wrbo_settings_group', 'wrbo_order_statuses', [
            'type'              => 'array',
            'sanitize_callback' => [ $this, 'sanitize_statuses' ],
            'default'           => [ 'wc-completed' ],
        ] );
        register_setting( 'wrbo_settings_group', 'wrbo_refund_tracking', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ] );
        register_setting( 'wrbo_settings_group', 'wrbo_only_marke', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => false,
        ] );
    }

    public function sanitize_mapping( $value ): array {
        if ( ! is_array( $value ) ) {
            return [];
        }
        $clean = [];
        $flat  = WRBO_Open_Categories::get_flat();
        foreach ( $value as $wc_cat_id => $open_code ) {
            $wc_cat_id = absint( $wc_cat_id );
            $open_code = sanitize_text_field( $open_code );
            if ( $wc_cat_id > 0 && ( '' === $open_code || isset( $flat[ $open_code ] ) ) ) {
                $clean[ $wc_cat_id ] = $open_code;
            }
        }
        return $clean;
    }

    public function sanitize_statuses( $value ): array {
        if ( ! is_array( $value ) ) {
            return [];
        }
        $allowed = array_keys( wc_get_order_statuses() );
        $clean   = [];
        foreach ( $value as $status ) {
            $status = sanitize_text_field( $status );
            if ( in_array( $status, $allowed, true ) ) {
                $clean[] = $status;
            }
        }
        return $clean;
    }

    public static function get_category_mapping(): array {
        return (array) get_option( 'wrbo_category_mapping', [] );
    }

    public static function get_order_statuses(): array {
        $statuses = (array) get_option( 'wrbo_order_statuses', [ 'wc-completed' ] );
        return empty( $statuses ) ? [ 'wc-completed' ] : $statuses;
    }

    public static function is_refund_tracking_enabled(): bool {
        return (bool) get_option( 'wrbo_refund_tracking', true );
    }

    /**
     * Get the OPEN code mapped to a WooCommerce category ID.
     * Walks up the category hierarchy until a mapping is found.
     */
    public static function get_open_code_for_product( int $product_id ): ?string {
        $mapping = self::get_category_mapping();
        if ( empty( $mapping ) ) {
            return null;
        }

        $terms = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'ids' ] );
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return null;
        }

        // Direct match first
        foreach ( $terms as $term_id ) {
            if ( isset( $mapping[ $term_id ] ) && '' !== $mapping[ $term_id ] ) {
                return $mapping[ $term_id ];
            }
        }

        // Walk up the hierarchy
        foreach ( $terms as $term_id ) {
            $ancestors = get_ancestors( $term_id, 'product_cat', 'taxonomy' );
            foreach ( $ancestors as $ancestor_id ) {
                if ( isset( $mapping[ $ancestor_id ] ) && '' !== $mapping[ $ancestor_id ] ) {
                    return $mapping[ $ancestor_id ];
                }
            }
        }

        return null;
    }
}

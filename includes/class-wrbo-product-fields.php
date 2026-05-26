<?php
defined( 'ABSPATH' ) || exit;

class WRBO_Product_Fields {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Simple product tab
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_net_weight_field' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_net_weight_field' ] );

        // Variable product: show on each variation
        add_action( 'woocommerce_variation_options_pricing', [ $this, 'add_variation_net_weight_field' ], 10, 3 );
        add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_net_weight_field' ], 10, 2 );
    }

    public function add_net_weight_field(): void {
        echo '<div class="options_group">';
        woocommerce_wp_text_input( [
            'id'                => '_wrbo_netto_gewicht',
            'label'             => __( 'Netto-gewicht (kg)', 'wrbo' ),
            'description'       => __( 'Het Netto-gewicht wordt gebruikt voor de recycle bijdrage.', 'wrbo' ),
            'desc_tip'          => true,
            'type'              => 'number',
            'custom_attributes' => [
                'step' => '0.001',
                'min'  => '0',
            ],
        ] );
        echo '</div>';
    }

    public function save_net_weight_field( int $post_id ): void {
        if ( ! current_user_can( 'edit_product', $post_id ) ) {
            return;
        }
        $value = isset( $_POST['_wrbo_netto_gewicht'] ) ? wc_format_decimal( sanitize_text_field( wp_unslash( $_POST['_wrbo_netto_gewicht'] ) ) ) : '';
        update_post_meta( $post_id, '_wrbo_netto_gewicht', $value );
    }

    public function add_variation_net_weight_field( int $loop, array $variation_data, \WP_Post $variation ): void {
        woocommerce_wp_text_input( [
            'id'                => "_wrbo_netto_gewicht_{$loop}",
            'name'              => "variable_wrbo_netto_gewicht[{$loop}]",
            'value'             => get_post_meta( $variation->ID, '_wrbo_netto_gewicht', true ),
            'label'             => __( 'Netto-gewicht (kg)', 'wrbo' ),
            'description'       => __( 'Het Netto-gewicht wordt gebruikt voor de recycle bijdrage.', 'wrbo' ),
            'desc_tip'          => true,
            'type'              => 'number',
            'custom_attributes' => [
                'step' => '0.001',
                'min'  => '0',
            ],
        ] );
    }

    public function save_variation_net_weight_field( int $variation_id, int $loop ): void {
        if ( ! current_user_can( 'edit_product', $variation_id ) ) {
            return;
        }
        if ( isset( $_POST['variable_wrbo_netto_gewicht'][ $loop ] ) ) {
            $value = wc_format_decimal( sanitize_text_field( wp_unslash( $_POST['variable_wrbo_netto_gewicht'][ $loop ] ) ) );
            update_post_meta( $variation_id, '_wrbo_netto_gewicht', $value );
        }
    }

    /**
     * Get the net weight for a product or variation.
     * Falls back to the parent if variant has no value.
     */
    public static function get_net_weight( int $product_id ): float {
        $value = get_post_meta( $product_id, '_wrbo_netto_gewicht', true );
        if ( '' !== $value && is_numeric( $value ) ) {
            return (float) $value;
        }
        // For variations: try parent
        $parent_id = wp_get_post_parent_id( $product_id );
        if ( $parent_id ) {
            $value = get_post_meta( $parent_id, '_wrbo_netto_gewicht', true );
            if ( '' !== $value && is_numeric( $value ) ) {
                return (float) $value;
            }
        }
        return 0.0;
    }
}

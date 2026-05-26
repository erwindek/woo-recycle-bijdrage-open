<?php
defined( 'ABSPATH' ) || exit;

class WRBO_Refunds {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'woocommerce_order_refunded', [ $this, 'capture_refund' ], 10, 2 );
    }

    /**
     * When an order is refunded, record a deduction for any item that was
     * already in a completed order (i.e. it may have been included in a prior report).
     */
    public function capture_refund( int $order_id, int $refund_id ): void {
        if ( ! WRBO_Settings::is_refund_tracking_enabled() ) {
            return;
        }

        $refund = wc_get_order( $refund_id );
        if ( ! $refund ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // Only track refunds on orders that would have been in a report
        $allowed_statuses = WRBO_Settings::get_order_statuses();
        // Strip wc- prefix for comparison
        $order_status = 'wc-' . $order->get_status();
        if ( ! in_array( $order_status, $allowed_statuses, true ) ) {
            return;
        }

        // Skip zero-value orders
        if ( (float) $order->get_total() <= 0 ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wrbo_refund_deductions';

        foreach ( $refund->get_items() as $item ) {
            /** @var WC_Order_Item_Product $item */
            $product_id   = $item->get_product_id();
            $variation_id = $item->get_variation_id();
            $qty          = abs( $item->get_quantity() );

            $lookup_id  = $variation_id ?: $product_id;
            $net_weight = WRBO_Product_Fields::get_net_weight( $lookup_id );

            // Check for existing deduction record for this refund item to avoid duplicates
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$table} WHERE refund_id = %d AND product_id = %d AND variation_id = %d",
                $refund_id, $product_id, $variation_id
            ) );
            if ( $exists ) {
                continue;
            }

            $wpdb->insert( $table, [
                'refund_id'    => $refund_id,
                'order_id'     => $order_id,
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
                'qty'          => $qty,
                'weight_kg'    => $net_weight * $qty,
                'refund_date'  => $refund->get_date_created() ? $refund->get_date_created()->date( 'Y-m-d H:i:s' ) : current_time( 'mysql' ),
                'deducted'     => 0,
            ], [ '%d', '%d', '%d', '%d', '%d', '%f', '%s', '%d' ] );
        }
    }

    /**
     * Get pending deductions (not yet assigned to a report period).
     * Returns array of rows from wrbo_refund_deductions.
     */
    public static function get_pending_deductions(): array {
        global $wpdb;
        $table = $wpdb->prefix . 'wrbo_refund_deductions';
        return $wpdb->get_results(
            "SELECT * FROM {$table} WHERE deducted = 0 ORDER BY refund_date ASC",
            ARRAY_A
        );
    }

    /**
     * Mark deductions as applied to a given period string (e.g. "2026-01_2026-04").
     */
    public static function mark_deducted( array $ids, string $period ): void {
        if ( empty( $ids ) ) {
            return;
        }
        global $wpdb;
        $table       = $wpdb->prefix . 'wrbo_refund_deductions';
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$table} SET deducted = 1, deducted_period = %s WHERE id IN ({$placeholders})",
            array_merge( [ $period ], $ids )
        ) );
    }

    /**
     * Get deductions that were applied to a specific period (for display in report).
     */
    public static function get_deductions_for_period( string $period ): array {
        global $wpdb;
        $table = $wpdb->prefix . 'wrbo_refund_deductions';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE deducted_period = %s ORDER BY refund_date ASC",
            $period
        ), ARRAY_A );
    }
}

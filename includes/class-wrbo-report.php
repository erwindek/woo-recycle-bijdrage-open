<?php
defined( 'ABSPATH' ) || exit;

class WRBO_Report {

    /**
     * Generate report data for a date range.
     *
     * Returns [
     *   'summary'          => [ open_code => [ 'open_code', 'open_label', 'wc_categories', 'qty', 'weight_kg' ] ],
     *   'detail'           => [ product_id => [ ...product detail row... ] ],
     *   'deductions'       => [ ...pending deduction rows... ],
     *   'period'           => string,
     *   'date_from'        => string,
     *   'date_to'          => string,
     * ]
     */
    public static function generate( string $date_from, string $date_to, bool $apply_deductions = false ): array {
        $allowed_statuses = WRBO_Settings::get_order_statuses();
        // Convert to statuses without wc- prefix for query
        $statuses = array_map( fn( $s ) => str_replace( 'wc-', '', $s ), $allowed_statuses );

        // Pre-compute summary category labels from the settings mapping (not from product data)
        $code_to_cats = WRBO_Settings::get_categories_by_open_code();

        $orders = wc_get_orders( [
            'status'       => $statuses,
            'date_created' => $date_from . '...' . $date_to . ' 23:59:59',
            'limit'        => -1,
            'return'       => 'objects',
            'type'         => 'shop_order',
        ] );

        $detail  = [];  // keyed by "product_id:variation_id"
        $summary = [];  // keyed by open_code

        foreach ( $orders as $order ) {
            // Skip zero-value orders
            if ( (float) $order->get_total() <= 0 ) {
                continue;
            }

            foreach ( $order->get_items() as $item ) {
                if ( ! $item instanceof WC_Order_Item_Product ) {
                    continue;
                }
                $product_id   = $item->get_product_id();
                $variation_id = $item->get_variation_id();
                $qty          = (int) $item->get_quantity();

                if ( $qty <= 0 ) {
                    continue;
                }

                $lookup_id  = $variation_id ?: $product_id;
                $net_weight = WRBO_Product_Fields::get_net_weight( $lookup_id );
                $open_code  = WRBO_Settings::get_open_code_for_product( $product_id );
                $detail_key = "{$product_id}:{$variation_id}";

                if ( ! isset( $detail[ $detail_key ] ) ) {
                    $product    = wc_get_product( $lookup_id );
                    $parent_cat = self::get_primary_category_name( $product_id );
                    $ean        = get_post_meta( $lookup_id, '_ean', true )
                                  ?: get_post_meta( $lookup_id, '_global_unique_id', true )
                                  ?: '';
                    $lev_ref    = get_post_meta( $lookup_id, '_supplier_ref', true )
                                  ?: get_post_meta( $lookup_id, '_sku_supplier', true )
                                  ?: '';

                    $detail[ $detail_key ] = [
                        'product_id'   => $product_id,
                        'variation_id' => $variation_id,
                        'sku'          => $product ? $product->get_sku() : '',
                        'name'         => $product ? $product->get_name() : "Product #{$product_id}",
                        'ean'          => $ean,
                        'lev_ref'      => $lev_ref,
                        'category'     => $parent_cat,
                        'net_weight'   => $net_weight,
                        'qty'          => 0,
                        'total_weight' => 0.0,
                        'open_code'    => $open_code,
                    ];
                }

                $detail[ $detail_key ]['qty']          += $qty;
                $detail[ $detail_key ]['total_weight']  += $net_weight * $qty;
            }
        }

        // Build summary per OPEN code (skip 'geen' — explicit no-contribution products)
        foreach ( $detail as $row ) {
            $code = $row['open_code'];
            if ( ! $code ) {
                $code = '__unmapped__';
            }
            if ( 'geen' === $code ) {
                continue; // explicitly excluded from reporting
            }
            if ( ! isset( $summary[ $code ] ) ) {
                $code_data = $code !== '__unmapped__' ? WRBO_Open_Categories::get_code( $code ) : null;
                $summary[ $code ] = [
                    'open_code'      => $code,
                    'open_label'     => $code_data ? $code_data['label'] : '— Niet gekoppeld —',
                    'eee_code'       => $code_data ? $code_data['eee_code'] : '',
                    // Use the categories from settings mapping, not product-level categories
                    'wc_categories'  => $code_to_cats[ $code ] ?? [],
                    'qty'            => 0,
                    'weight_kg'      => 0.0,
                ];
            }
            $summary[ $code ]['qty']       += $row['qty'];
            $summary[ $code ]['weight_kg'] += $row['total_weight'];
        }

        // Handle pending refund deductions
        $pending_deductions = [];
        if ( WRBO_Settings::is_refund_tracking_enabled() ) {
            $pending_deductions = WRBO_Refunds::get_pending_deductions();

            if ( $apply_deductions && ! empty( $pending_deductions ) ) {
                $period  = self::period_key( $date_from, $date_to );
                $ded_ids = [];

                foreach ( $pending_deductions as $ded ) {
                    $pid      = (int) $ded['product_id'];
                    $vid      = (int) $ded['variation_id'];
                    $open_code = WRBO_Settings::get_open_code_for_product( $pid ) ?? '__unmapped__';
                    $qty      = (int) $ded['qty'];
                    $weight   = (float) $ded['weight_kg'];

                    if ( isset( $summary[ $open_code ] ) ) {
                        $summary[ $open_code ]['qty']       -= $qty;
                        $summary[ $open_code ]['weight_kg'] -= $weight;
                    }

                    $det_key = "{$pid}:{$vid}";
                    if ( isset( $detail[ $det_key ] ) ) {
                        $detail[ $det_key ]['qty']          -= $qty;
                        $detail[ $det_key ]['total_weight'] -= $weight;
                    }

                    $ded_ids[] = (int) $ded['id'];
                }

                WRBO_Refunds::mark_deducted( $ded_ids, $period );
                // Re-fetch so we display the applied ones
                $pending_deductions = WRBO_Refunds::get_deductions_for_period( $period );
            }
        }

        // Sort summary by OPEN code
        ksort( $summary );

        return [
            'summary'     => $summary,
            'detail'      => $detail,
            'deductions'  => $pending_deductions,
            'period'      => self::period_key( $date_from, $date_to ),
            'date_from'   => $date_from,
            'date_to'     => $date_to,
        ];
    }

    private static function get_primary_category_name( int $product_id ): string {
        $terms = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'all' ] );
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }
        // Prefer deepest category
        usort( $terms, fn( $a, $b ) => $b->parent - $a->parent );
        return $terms[0]->name;
    }

    public static function period_key( string $date_from, string $date_to ): string {
        return sanitize_key( $date_from . '_' . $date_to );
    }
}

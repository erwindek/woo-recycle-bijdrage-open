<?php
defined( 'ABSPATH' ) || exit;

$updated = isset( $_GET['wrbo_updated'] ) ? absint( $_GET['wrbo_updated'] ) : null;
$skipped = isset( $_GET['wrbo_skipped'] ) ? absint( $_GET['wrbo_skipped'] ) : null;
$errors  = isset( $_GET['wrbo_errors'] ) && $_GET['wrbo_errors'] ? explode( '|', urldecode( sanitize_text_field( $_GET['wrbo_errors'] ) ) ) : [];
$wrbo_error = isset( $_GET['wrbo_error'] ) ? sanitize_text_field( urldecode( $_GET['wrbo_error'] ) ) : '';
?>
<div class="wrap wrbo-wrap">
    <h1><?php esc_html_e( 'Importeer netto-gewichten', 'wrbo' ); ?></h1>

    <?php if ( null !== $updated ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php printf(
                esc_html__( 'Import voltooid: %d product(en) bijgewerkt, %d overgeslagen.', 'wrbo' ),
                $updated,
                $skipped
            ); ?></p>
        </div>
    <?php endif; ?>

    <?php if ( $wrbo_error ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html( $wrbo_error ); ?></p>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $errors ) ) : ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong><?php esc_html_e( 'Waarschuwingen:', 'wrbo' ); ?></strong></p>
            <ul>
                <?php foreach ( $errors as $error ) : ?>
                    <li><?php echo esc_html( $error ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="wrbo-card">
        <h2><?php esc_html_e( 'CSV importeren', 'wrbo' ); ?></h2>
        <p><?php esc_html_e( 'Upload een CSV-bestand met de netto-gewichten per product. Het bestand moet de volgende kolommen bevatten (scheidingsteken: , of ;):', 'wrbo' ); ?></p>
        <table class="wrbo-example-table">
            <thead>
                <tr>
                    <th>woo_id</th>
                    <th>netto_gewicht</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>12345</td><td>11.5</td></tr>
                <tr><td>67890</td><td>8,25</td></tr>
            </tbody>
        </table>
        <p class="description"><?php esc_html_e( 'Zowel punt (.) als komma (,) zijn toegestaan als decimaalteken. De eerste rij wordt behandeld als koptekst.', 'wrbo' ); ?></p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="wrbo_import_csv">
            <?php wp_nonce_field( 'wrbo_import_csv' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="wrbo_csv"><?php esc_html_e( 'CSV-bestand', 'wrbo' ); ?></label></th>
                    <td>
                        <input type="file" name="wrbo_csv" id="wrbo_csv" accept=".csv,text/csv" required>
                    </td>
                </tr>
            </table>
            <?php submit_button( __( 'Importeer gewichten', 'wrbo' ) ); ?>
        </form>
    </div>

    <div class="wrbo-card">
        <h2><?php esc_html_e( 'Products zonder netto-gewicht', 'wrbo' ); ?></h2>
        <p><?php esc_html_e( 'Onderstaande producten (met orders in de afgelopen 12 maanden) hebben nog geen netto-gewicht ingesteld.', 'wrbo' ); ?></p>
        <?php
        // Show products without net weight that had orders in the last 12 months
        $months_ago = date( 'Y-m-d', strtotime( '-12 months' ) );
        $orders_recent = wc_get_orders( [
            'status'       => array_map( fn($s) => str_replace('wc-', '', $s), WRBO_Settings::get_order_statuses() ),
            'date_created' => $months_ago . '...' . date( 'Y-m-d' ),
            'limit'        => -1,
            'return'       => 'objects',
            'type'         => 'shop_order',
        ] );

        $missing = [];
        foreach ( $orders_recent as $order ) {
            foreach ( $order->get_items() as $item ) {
                $vid = $item->get_variation_id() ?: $item->get_product_id();
                if ( isset( $missing[ $vid ] ) ) {
                    continue;
                }
                $w = get_post_meta( $vid, '_wrbo_netto_gewicht', true );
                if ( '' === $w || ! is_numeric( $w ) || (float) $w <= 0 ) {
                    $p = wc_get_product( $vid );
                    $missing[ $vid ] = [
                        'id'  => $vid,
                        'sku' => $p ? $p->get_sku() : '',
                        'name' => $p ? $p->get_name() : "Product #{$vid}",
                    ];
                }
            }
        }
        ?>
        <?php if ( empty( $missing ) ) : ?>
            <p class="wrbo-ok"><?php esc_html_e( '✓ Alle producten met recente orders hebben een netto-gewicht.', 'wrbo' ); ?></p>
        <?php else : ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Product ID', 'wrbo' ); ?></th>
                        <th><?php esc_html_e( 'SKU', 'wrbo' ); ?></th>
                        <th><?php esc_html_e( 'Naam', 'wrbo' ); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $missing as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html( $row['id'] ); ?></td>
                            <td><?php echo esc_html( $row['sku'] ); ?></td>
                            <td><?php echo esc_html( $row['name'] ); ?></td>
                            <td><a href="<?php echo esc_url( get_edit_post_link( $row['id'] ) ); ?>" class="button button-small"><?php esc_html_e( 'Bewerken', 'wrbo' ); ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

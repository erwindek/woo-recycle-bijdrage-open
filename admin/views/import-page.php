<?php
defined( 'ABSPATH' ) || exit;

$updated = isset( $_GET['wrbo_updated'] ) ? absint( $_GET['wrbo_updated'] ) : null;
$skipped = isset( $_GET['wrbo_skipped'] ) ? absint( $_GET['wrbo_skipped'] ) : null;
$errors  = isset( $_GET['wrbo_errors'] ) && $_GET['wrbo_errors'] ? explode( '|', urldecode( sanitize_text_field( wp_unslash( $_GET['wrbo_errors'] ) ) ) ) : [];
$wrbo_error = isset( $_GET['wrbo_error'] ) ? sanitize_text_field( urldecode( wp_unslash( $_GET['wrbo_error'] ) ) ) : '';
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
        <p class="description"><?php esc_html_e( 'Zowel punt (.) als komma (,) zijn toegestaan als decimaalteken. De eerste rij wordt behandeld als koptekst. UTF-8 BOM (Excel) wordt automatisch verwijderd.', 'wrbo' ); ?></p>

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
        <h2><?php esc_html_e( 'Gepubliceerde producten zonder netto-gewicht', 'wrbo' ); ?></h2>
        <p><?php esc_html_e( 'Onderstaande gepubliceerde producten hebben nog geen netto-gewicht ingesteld (max. 100 weergegeven).', 'wrbo' ); ?></p>
        <?php
        global $wpdb;

        // Direct meta query – avoids loading orders, works fast on large stores
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $missing = $wpdb->get_results(
            "SELECT p.ID, p.post_title, pm_sku.meta_value AS sku
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm
                 ON p.ID = pm.post_id AND pm.meta_key = '_wrbo_netto_gewicht'
             LEFT JOIN {$wpdb->postmeta} pm_sku
                 ON p.ID = pm_sku.post_id AND pm_sku.meta_key = '_sku'
             WHERE p.post_type IN ('product', 'product_variation')
               AND p.post_status = 'publish'
               AND (pm.meta_value IS NULL
                    OR pm.meta_value = ''
                    OR CAST(pm.meta_value AS DECIMAL(10,4)) <= 0)
             ORDER BY p.post_title ASC
             LIMIT 100",
            ARRAY_A
        );
        ?>
        <?php if ( empty( $missing ) ) : ?>
            <p class="wrbo-ok"><?php esc_html_e( '✓ Alle gepubliceerde producten hebben een netto-gewicht.', 'wrbo' ); ?></p>
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
                            <td><?php echo esc_html( $row['ID'] ); ?></td>
                            <td><?php echo esc_html( $row['sku'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $row['post_title'] ); ?></td>
                            <td><a href="<?php echo esc_url( get_edit_post_link( (int) $row['ID'] ) ); ?>" class="button button-small"><?php esc_html_e( 'Bewerken', 'wrbo' ); ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

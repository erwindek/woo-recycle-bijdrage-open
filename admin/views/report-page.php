<?php
defined( 'ABSPATH' ) || exit;

$date_from        = isset( $_GET['date_from'] ) ? sanitize_text_field( $_GET['date_from'] ) : date( 'Y-01-01' );
$date_to          = isset( $_GET['date_to'] )   ? sanitize_text_field( $_GET['date_to'] )   : date( 'Y-m-d' );
$apply_deductions = ! empty( $_GET['apply_deductions'] ) && '1' === $_GET['apply_deductions'];
$show_report      = isset( $_GET['date_from'] );

$report_data = null;
$deductions  = [];
if ( $show_report ) {
    $report_data = WRBO_Report::generate( $date_from, $date_to, $apply_deductions );
}

$refund_enabled  = WRBO_Settings::is_refund_tracking_enabled();
$pending_count   = $refund_enabled && ! $apply_deductions ? count( WRBO_Refunds::get_pending_deductions() ) : 0;
?>
<div class="wrap wrbo-wrap">
    <h1><?php esc_html_e( 'Rapportage – Recycle Bijdrage OPEN', 'wrbo' ); ?></h1>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="wrbo_generate_report">
        <?php wp_nonce_field( 'wrbo_generate_report' ); ?>
        <div class="wrbo-filter-bar">
            <label>
                <?php esc_html_e( 'Van', 'wrbo' ); ?>
                <input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" required>
            </label>
            <label>
                <?php esc_html_e( 'Tot', 'wrbo' ); ?>
                <input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" required>
            </label>
            <?php if ( $refund_enabled ) : ?>
                <label class="wrbo-deduction-toggle">
                    <input type="checkbox" name="apply_deductions" value="1" <?php checked( $apply_deductions ); ?>>
                    <?php esc_html_e( 'Terugbetalingen aftrekken van deze periode', 'wrbo' ); ?>
                    <?php if ( $pending_count > 0 ) : ?>
                        <span class="wrbo-badge"><?php echo esc_html( $pending_count ); ?></span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            <?php submit_button( __( 'Rapport genereren', 'wrbo' ), 'primary', 'submit', false ); ?>
        </div>
    </form>

    <?php if ( $show_report && $report_data ) : ?>
        <div class="wrbo-report">
            <h2><?php printf(
                esc_html__( 'Rapportage periode: %s t/m %s', 'wrbo' ),
                esc_html( $date_from ),
                esc_html( $date_to )
            ); ?></h2>

            <?php
            // Show deduction notice
            if ( $refund_enabled ) :
                $deductions = $report_data['deductions'];
                if ( ! empty( $deductions ) && $apply_deductions ) :
            ?>
                <div class="notice notice-warning inline">
                    <p><?php printf(
                        esc_html__( '%d terugbetaling(en) zijn van deze periode afgetrokken.', 'wrbo' ),
                        count( $deductions )
                    ); ?></p>
                </div>
            <?php
                elseif ( $pending_count > 0 && ! $apply_deductions ) :
            ?>
                <div class="notice notice-info inline">
                    <p><?php printf(
                        esc_html__( 'Er zijn %d openstaande terugbetaling(en) die nog niet zijn afgetrokken. Vink "Terugbetalingen aftrekken" aan om ze op dit rapport toe te passen.', 'wrbo' ),
                        (int) $pending_count
                    ); ?></p>
                </div>
            <?php
                endif;
            endif;
            ?>

            <!-- ========================================================
                 SECTIE 1: Opgave naar OPEN
                 ======================================================== -->
            <h3><?php esc_html_e( 'Opgave naar Stichting OPEN', 'wrbo' ); ?></h3>

            <?php
            $summary = $report_data['summary'];
            $mapped  = array_filter( $summary, fn( $r ) => $r['open_code'] !== '__unmapped__' );
            $unmapped = array_filter( $summary, fn( $r ) => $r['open_code'] === '__unmapped__' );
            ?>

            <?php if ( empty( $mapped ) ) : ?>
                <p class="wrbo-empty"><?php esc_html_e( 'Geen gegevens gevonden voor de gekozen periode.', 'wrbo' ); ?></p>
            <?php else : ?>
                <table class="widefat wrbo-summary-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Code OPEN', 'wrbo' ); ?></th>
                            <th><?php esc_html_e( 'Categorie OPEN', 'wrbo' ); ?></th>
                            <th><?php esc_html_e( 'Onze categorie', 'wrbo' ); ?></th>
                            <th class="wrbo-num"><?php esc_html_e( 'Aantallen', 'wrbo' ); ?></th>
                            <th class="wrbo-num"><?php esc_html_e( 'Gewicht', 'wrbo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $mapped as $row ) :
                            $wc_cats = implode( ', ', $row['wc_categories'] );
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html( $row['open_code'] ); ?></strong></td>
                                <td><?php echo esc_html( $row['open_label'] ); ?></td>
                                <td><?php echo esc_html( $wc_cats ); ?></td>
                                <td class="wrbo-num"><?php echo esc_html( number_format( $row['qty'], 0, ',', '.' ) ); ?></td>
                                <td class="wrbo-num"><?php echo esc_html( number_format( $row['weight_kg'], 3, ',', '.' ) ); ?> kg</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="wrbo-totals">
                            <td colspan="3"><strong><?php esc_html_e( 'Totaal', 'wrbo' ); ?></strong></td>
                            <td class="wrbo-num"><strong><?php echo esc_html( number_format( array_sum( array_column( $mapped, 'qty' ) ), 0, ',', '.' ) ); ?></strong></td>
                            <td class="wrbo-num"><strong><?php echo esc_html( number_format( array_sum( array_column( $mapped, 'weight_kg' ) ), 3, ',', '.' ) ); ?> kg</strong></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="wrbo-export-bar">
                    <button type="button" class="button wrbo-copy-csv" data-target="wrbo-summary-table">
                        <?php esc_html_e( 'Kopieer als CSV', 'wrbo' ); ?>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $unmapped ) ) : ?>
                <div class="notice notice-warning inline">
                    <p><?php
                    printf(
                        wp_kses(
                            __( '%d productcategorie(ën) zonder OPEN-koppeling. Stel de koppeling in via <a href="%s">Instellingen</a>.', 'wrbo' ),
                            [ 'a' => [ 'href' => [] ] ]
                        ),
                        (int) count( $unmapped ),
                        esc_url( admin_url( 'admin.php?page=wrbo-settings' ) )
                    ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( $apply_deductions && ! empty( $deductions ) ) : ?>
                <h3><?php esc_html_e( 'Verwerkte terugbetalingen (afgetrokken van deze periode)', 'wrbo' ); ?></h3>
                <table class="widefat wrbo-deductions-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Bestelling', 'wrbo' ); ?></th>
                            <th><?php esc_html_e( 'Product ID', 'wrbo' ); ?></th>
                            <th><?php esc_html_e( 'Datum terugbetaling', 'wrbo' ); ?></th>
                            <th class="wrbo-num"><?php esc_html_e( 'Aantal', 'wrbo' ); ?></th>
                            <th class="wrbo-num"><?php esc_html_e( 'Gewicht (kg)', 'wrbo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $deductions as $ded ) : ?>
                            <tr>
                                <td><a href="<?php echo esc_url( get_edit_post_link( $ded['order_id'] ) ); ?>">#<?php echo esc_html( $ded['order_id'] ); ?></a></td>
                                <td><?php echo esc_html( $ded['product_id'] ); ?></td>
                                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $ded['refund_date'] ) ) ); ?></td>
                                <td class="wrbo-num"><?php echo esc_html( $ded['qty'] ); ?></td>
                                <td class="wrbo-num"><?php echo esc_html( number_format( $ded['weight_kg'], 3, ',', '.' ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- ========================================================
                 SECTIE 2: Productdetails
                 ======================================================== -->
            <h3 style="margin-top:2em;"><?php esc_html_e( 'Productdetails – onderbouwing van de opgave', 'wrbo' ); ?></h3>

            <?php
            $detail = $report_data['detail'];
            // Sort by category then sku
            uasort( $detail, function ( $a, $b ) {
                $cat_cmp = strcmp( $a['category'], $b['category'] );
                return $cat_cmp !== 0 ? $cat_cmp : strcmp( $a['sku'], $b['sku'] );
            } );
            ?>

            <?php if ( empty( $detail ) ) : ?>
                <p class="wrbo-empty"><?php esc_html_e( 'Geen productdetails beschikbaar.', 'wrbo' ); ?></p>
            <?php else : ?>
                <div class="wrbo-detail-filter">
                    <input type="search" id="wrbo-detail-search" placeholder="<?php esc_attr_e( 'Zoeken...', 'wrbo' ); ?>">
                </div>
                <div class="wrbo-table-scroll">
                    <table class="widefat wrbo-detail-table" id="wrbo-detail-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Type (SKU)', 'wrbo' ); ?></th>
                                <th><?php esc_html_e( 'Product ID Woo', 'wrbo' ); ?></th>
                                <th><?php esc_html_e( 'EAN', 'wrbo' ); ?></th>
                                <th><?php esc_html_e( 'Lev. Ref.', 'wrbo' ); ?></th>
                                <th><?php esc_html_e( 'Categorie', 'wrbo' ); ?></th>
                                <th><?php esc_html_e( 'OPEN code', 'wrbo' ); ?></th>
                                <th class="wrbo-num"><?php esc_html_e( 'Netto gewicht (kg)', 'wrbo' ); ?></th>
                                <th class="wrbo-num"><?php esc_html_e( 'Totaal stuks', 'wrbo' ); ?></th>
                                <th class="wrbo-num"><?php esc_html_e( 'Totaal gewicht (kg)', 'wrbo' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $detail as $row ) :
                                $edit_link = get_edit_post_link( $row['variation_id'] ?: $row['product_id'] );
                            ?>
                                <tr>
                                    <td>
                                        <?php if ( $edit_link ) : ?>
                                            <a href="<?php echo esc_url( $edit_link ); ?>"><?php echo esc_html( $row['sku'] ?: "#{$row['product_id']}" ); ?></a>
                                        <?php else : ?>
                                            <?php echo esc_html( $row['sku'] ?: "#{$row['product_id']}" ); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html( $row['variation_id'] ?: $row['product_id'] ); ?></td>
                                    <td><?php echo esc_html( $row['ean'] ); ?></td>
                                    <td><?php echo esc_html( $row['lev_ref'] ); ?></td>
                                    <td><?php echo esc_html( $row['category'] ); ?></td>
                                    <td><?php
                                        $oc = $row['open_code'] ?? '';
                                        if ( 'geen' === $oc ) {
                                            echo '<span class="wrbo-geen">' . esc_html__( 'Geen bijdrage', 'wrbo' ) . '</span>';
                                        } else {
                                            echo esc_html( $oc ?: '—' );
                                        }
                                    ?></td>
                                    <td class="wrbo-num <?php echo $row['net_weight'] <= 0 ? 'wrbo-missing-weight' : ''; ?>">
                                        <?php echo esc_html( $row['net_weight'] > 0 ? number_format( $row['net_weight'], 3, ',', '.' ) : '—' ); ?>
                                    </td>
                                    <td class="wrbo-num"><?php echo esc_html( number_format( $row['qty'], 0, ',', '.' ) ); ?></td>
                                    <td class="wrbo-num"><?php echo esc_html( $row['total_weight'] > 0 ? number_format( $row['total_weight'], 3, ',', '.' ) : '—' ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="wrbo-totals">
                                <td colspan="7"><strong><?php esc_html_e( 'Totaal', 'wrbo' ); ?></strong></td>
                                <td class="wrbo-num"><strong><?php echo esc_html( number_format( array_sum( array_column( $detail, 'qty' ) ), 0, ',', '.' ) ); ?></strong></td>
                                <td class="wrbo-num"><strong><?php echo esc_html( number_format( array_sum( array_column( $detail, 'total_weight' ) ), 3, ',', '.' ) ); ?> kg</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="wrbo-export-bar">
                    <button type="button" class="button wrbo-copy-csv" data-target="wrbo-detail-table">
                        <?php esc_html_e( 'Kopieer detail als CSV', 'wrbo' ); ?>
                    </button>
                </div>
            <?php endif; ?>

        </div><!-- .wrbo-report -->
    <?php endif; ?>
</div>

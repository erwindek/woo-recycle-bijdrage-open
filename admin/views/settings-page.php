<?php
defined( 'ABSPATH' ) || exit;

$all_wc_cats   = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name' ] );
$all_open_cats = WRBO_Open_Categories::get_all();
$all_open_flat = WRBO_Open_Categories::get_flat();
$mapping       = WRBO_Settings::get_category_mapping();
$statuses      = wc_get_order_statuses();
$saved_statuses = WRBO_Settings::get_order_statuses();
$refund_tracking = WRBO_Settings::is_refund_tracking_enabled();
?>
<div class="wrap wrbo-wrap">
    <h1><?php esc_html_e( 'Instellingen – Recycle Bijdrage OPEN', 'wrbo' ); ?></h1>

    <?php settings_errors( 'wrbo_settings_group' ); ?>

    <form method="post" action="options.php">
        <?php settings_fields( 'wrbo_settings_group' ); ?>

        <h2 class="title"><?php esc_html_e( 'Orderstatussen voor rapportage', 'wrbo' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Selecteer de orderstatussen die worden meegeteld in de rapportage.', 'wrbo' ); ?></p>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e( 'Orderstatussen', 'wrbo' ); ?></th>
                <td>
                    <?php foreach ( $statuses as $slug => $label ) : ?>
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox"
                                   name="wrbo_order_statuses[]"
                                   value="<?php echo esc_attr( $slug ); ?>"
                                <?php checked( in_array( $slug, $saved_statuses, true ) ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>

        <h2 class="title"><?php esc_html_e( 'Terugbetalingen bijhouden', 'wrbo' ); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e( 'Terugbetalingen aftrekken', 'wrbo' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wrbo_refund_tracking" value="1" <?php checked( $refund_tracking ); ?>>
                        <?php esc_html_e( 'Bijhouden en aftrekken van terugbetaalde producten op volgende aangifte-periode.', 'wrbo' ); ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'Als ingeschakeld worden terugbetaalde orders (op reeds afgeronde periodes) zichtbaar op de rapportagepagina en kunnen ze worden afgetrokken.', 'wrbo' ); ?></p>
                </td>
            </tr>
        </table>

        <h2 class="title"><?php esc_html_e( 'Categorie-koppeling: WooCommerce → Stichting OPEN', 'wrbo' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Koppel elke WooCommerce productcategorie aan een OPEN-code. Subcategorieën erven de koppeling van de bovenliggende categorie als ze zelf geen koppeling hebben.', 'wrbo' ); ?></p>

        <table class="widefat wrbo-mapping-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'WooCommerce categorie', 'wrbo' ); ?></th>
                    <th><?php esc_html_e( 'OPEN code', 'wrbo' ); ?></th>
                    <th><?php esc_html_e( 'OPEN categorie naam', 'wrbo' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( is_wp_error( $all_wc_cats ) || empty( $all_wc_cats ) ) : ?>
                    <tr><td colspan="3"><?php esc_html_e( 'Geen productcategorieën gevonden.', 'wrbo' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $all_wc_cats as $cat ) :
                        $current_code = $mapping[ $cat->term_id ] ?? '';
                        $indent       = str_repeat( '&nbsp;&nbsp;&nbsp;', $cat->parent ? 1 : 0 );
                    ?>
                        <tr>
                            <td><?php echo $indent; ?><?php echo esc_html( $cat->name ); ?> <small class="wrbo-cat-id">(ID: <?php echo esc_html( $cat->term_id ); ?>)</small></td>
                            <td>
                                <select name="wrbo_category_mapping[<?php echo esc_attr( $cat->term_id ); ?>]"
                                        class="wrbo-open-code-select"
                                        data-selected="<?php echo esc_attr( $current_code ); ?>">
                                    <option value=""><?php esc_html_e( '— Niet gekoppeld —', 'wrbo' ); ?></option>
                                    <?php foreach ( $all_open_cats as $eee_code => $group ) : ?>
                                        <optgroup label="<?php echo esc_attr( $eee_code . ' – ' . $group['label'] ); ?>">
                                            <?php foreach ( $group['codes'] as $code => $cdata ) : ?>
                                                <option value="<?php echo esc_attr( $code ); ?>"
                                                    <?php selected( $current_code, $code ); ?>>
                                                    <?php echo esc_html( $code . ' – ' . $cdata['label'] ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="wrbo-open-label">
                                <?php echo esc_html( $current_code ? ( $all_open_flat[ $current_code ] ?? '' ) : '' ); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php submit_button( __( 'Instellingen opslaan', 'wrbo' ) ); ?>
    </form>
</div>

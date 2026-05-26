<?php
defined( 'ABSPATH' ) || exit;

class WRBO_Import {

    /**
     * Process uploaded CSV file.
     * Expected columns: woo_id, netto_gewicht
     * Returns [ 'updated' => int, 'skipped' => int, 'errors' => string[] ]
     */
    public static function process_csv( string $file_path ): array {
        $result = [ 'updated' => 0, 'skipped' => 0, 'errors' => [] ];

        if ( ! file_exists( $file_path ) ) {
            $result['errors'][] = __( 'Bestand niet gevonden.', 'wrbo' );
            return $result;
        }

        $handle = fopen( $file_path, 'r' );
        if ( ! $handle ) {
            $result['errors'][] = __( 'Bestand kon niet worden geopend.', 'wrbo' );
            return $result;
        }

        // Read header row
        $header = fgetcsv( $handle, 0, ';' );
        if ( ! $header ) {
            $header = fgetcsv( $handle, 0, ',' );
        }
        if ( ! $header ) {
            fclose( $handle );
            $result['errors'][] = __( 'Leeg of ongeldig CSV-bestand.', 'wrbo' );
            return $result;
        }

        // Detect delimiter by re-opening if needed
        rewind( $handle );
        $first_line = fgets( $handle );
        rewind( $handle );
        $delimiter = ( substr_count( $first_line, ';' ) >= substr_count( $first_line, ',' ) ) ? ';' : ',';

        $header = fgetcsv( $handle, 0, $delimiter );
        // Strip UTF-8 BOM that Excel adds to CSV exports
        if ( $header ) {
            $header[0] = ltrim( $header[0], "\xEF\xBB\xBF" );
        }
        $header = array_map( 'strtolower', array_map( 'trim', $header ) );

        // Map column names
        $col_id = self::find_column( $header, [ 'woo_id', 'woo id', 'product_id', 'id' ] );
        $col_wt = self::find_column( $header, [ 'netto_gewicht', 'netto gewicht', 'nettogewicht', 'gewicht', 'weight' ] );

        if ( false === $col_id ) {
            fclose( $handle );
            $result['errors'][] = __( 'Kolom "woo_id" niet gevonden in CSV. Verwachte kolommen: woo_id, netto_gewicht.', 'wrbo' );
            return $result;
        }
        if ( false === $col_wt ) {
            fclose( $handle );
            $result['errors'][] = __( 'Kolom "netto_gewicht" niet gevonden in CSV. Verwachte kolommen: woo_id, netto_gewicht.', 'wrbo' );
            return $result;
        }

        $row_num = 1;
        while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
            $row_num++;
            if ( ! isset( $row[ $col_id ], $row[ $col_wt ] ) ) {
                $result['skipped']++;
                continue;
            }

            $woo_id = absint( trim( $row[ $col_id ] ) );
            // Allow comma as decimal separator
            $weight_raw = str_replace( ',', '.', trim( $row[ $col_wt ] ) );
            $weight     = is_numeric( $weight_raw ) ? (float) $weight_raw : false;

            if ( ! $woo_id ) {
                $result['errors'][] = sprintf( __( 'Rij %d: Ongeldig WooCommerce ID "%s".', 'wrbo' ), $row_num, esc_html( $row[ $col_id ] ) );
                $result['skipped']++;
                continue;
            }

            if ( false === $weight || $weight < 0 ) {
                $result['errors'][] = sprintf( __( 'Rij %d (ID %d): Ongeldig gewicht "%s".', 'wrbo' ), $row_num, $woo_id, esc_html( $row[ $col_wt ] ) );
                $result['skipped']++;
                continue;
            }

            // Verify post exists and is a product
            $post = get_post( $woo_id );
            if ( ! $post || ! in_array( $post->post_type, [ 'product', 'product_variation' ], true ) ) {
                $result['errors'][] = sprintf( __( 'Rij %d: Product met ID %d niet gevonden.', 'wrbo' ), $row_num, $woo_id );
                $result['skipped']++;
                continue;
            }

            update_post_meta( $woo_id, '_wrbo_netto_gewicht', wc_format_decimal( $weight ) );
            $result['updated']++;
        }

        fclose( $handle );
        return $result;
    }

    private static function find_column( array $header, array $candidates ) {
        foreach ( $candidates as $candidate ) {
            $key = array_search( $candidate, $header, true );
            if ( false !== $key ) {
                return $key;
            }
        }
        return false;
    }
}

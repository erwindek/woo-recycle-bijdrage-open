<?php
defined( 'ABSPATH' ) || exit;

/**
 * Static data: all Stichting OPEN EEE categories and subcodes.
 */
class WRBO_Open_Categories {

    /**
     * Returns the full OPEN category tree.
     * Structure: [ 'EEE-XX' => [ 'label' => '...', 'codes' => [ 'Ex/XX' => [...] ] ] ]
     */
    public static function get_all(): array {
        return [
            'EEE-01' => [
                'label' => 'Warmte- of koude-uitwisselende apparatuur',
                'codes' => [
                    'E1/01' => [ 'label' => 'Koel- en vriesapparatuur (huishoudelijk)',          'tarief' => 0.35, 'eenheid' => 'kg'   ],
                    'E1/02' => [ 'label' => 'Wasdrogers (met warmtepomp)',                        'tarief' => 8.25, 'eenheid' => 'stuk' ],
                    'E1/03' => [ 'label' => 'Airco-apparatuur (los)',                             'tarief' => 2.42, 'eenheid' => 'stuk' ],
                    'E1/04' => [ 'label' => 'Airco-apparatuur (inbouw) en warmtepompen',          'tarief' => 0.02, 'eenheid' => 'kg'   ],
                    'E1/05' => [ 'label' => 'Koel- en vriesapparatuur (professioneel)',           'tarief' => 0.15, 'eenheid' => 'kg'   ],
                    'E1/06' => [ 'label' => 'Automaten (gekoeld)',                                'tarief' => 1.60, 'eenheid' => 'kg'   ],
                ],
            ],
            'EEE-02' => [
                'label' => 'Schermen, monitors en apparatuur met schermen die een oppervlakte hebben van meer dan 100 cm²',
                'codes' => [
                    'E2/01' => [ 'label' => "TV's en displays (flatscreen)",                     'tarief' => 0.45, 'eenheid' => 'kg' ],
                    'E2/02' => [ 'label' => 'Monitoren (flatscreen)',                            'tarief' => 0.22, 'eenheid' => 'kg' ],
                    'E2/03' => [ 'label' => 'Laptops',                                           'tarief' => 0.07, 'eenheid' => 'kg' ],
                    'E2/04' => [ 'label' => 'Tablets en navigatiesystemen',                      'tarief' => 0.07, 'eenheid' => 'kg' ],
                ],
            ],
            'EEE-03' => [
                'label' => 'Lampen',
                'codes' => [
                    'E3/04' => [ 'label' => 'Ledlampen (incl. led TL)',                          'tarief' => 0.05, 'eenheid' => 'stuk' ],
                    'E3/05' => [ 'label' => 'Spaar- en gasontladingslampen',                     'tarief' => 0.14, 'eenheid' => 'stuk' ],
                    'E3/06' => [ 'label' => 'TL-lampen (excl. led TL)',                          'tarief' => 0.14, 'eenheid' => 'stuk' ],
                ],
            ],
            'EEE-04' => [
                'label' => 'Grote apparatuur (met een buitenafmeting van meer dan 50 cm)',
                'codes' => [
                    'E4/01' => [ 'label' => 'Afzuigkappen - huishoudelijk',                                                              'tarief' => 3.51,  'eenheid' => 'stuk' ],
                    'E4/02' => [ 'label' => 'Barbecues, grill- en kookplaten (>50 cm) - huishoudelijk',                                  'tarief' => 3.51,  'eenheid' => 'stuk' ],
                    'E4/03' => [ 'label' => 'Magnetrons en ovens (>50 cm) - huishoudelijk',                                             'tarief' => 3.51,  'eenheid' => 'stuk' ],
                    'E4/04' => [ 'label' => 'Fornuizen - huishoudelijk',                                                                 'tarief' => 3.51,  'eenheid' => 'stuk' ],
                    'E4/05' => [ 'label' => 'Vaatwassers - huishoudelijk',                                                              'tarief' => 3.51,  'eenheid' => 'stuk' ],
                    'E4/06' => [ 'label' => 'Wasmachines',                                                                              'tarief' => 12.73, 'eenheid' => 'stuk' ],
                    'E4/07' => [ 'label' => 'Wasdrogers (zonder warmtepomp)',                                                           'tarief' => 8.25,  'eenheid' => 'stuk' ],
                    'E4/08' => [ 'label' => 'Huishoud-, keuken- en verzorgingsapparatuur (>50 cm)',                                     'tarief' => 0.33,  'eenheid' => 'stuk' ],
                    'E4/09' => [ 'label' => 'Stofzuigers en vloerreinigers (>50 cm)',                                                   'tarief' => 0.37,  'eenheid' => 'stuk' ],
                    'E4/10' => [ 'label' => 'Ketels, boilers en geisers (>50 cm)',                                                      'tarief' => 0.04,  'eenheid' => 'kg'   ],
                    'E4/11' => [ 'label' => 'Zonnebanken',                                                                              'tarief' => 3.51,  'eenheid' => 'stuk' ],
                    'E4/12' => [ 'label' => 'Ventilatie-, recirculatie- en luchtbehandelingsinstallaties (>50 cm | >150 m³/uur)',       'tarief' => 0.08,  'eenheid' => 'kg'   ],
                    'E4/13' => [ 'label' => 'Ventilatie-, recirculatie- en luchtbehandelingsinstallaties (>50 cm | ≤150 m³/uur)',       'tarief' => 0.08,  'eenheid' => 'kg'   ],
                    'E4/14' => [ 'label' => 'Ventilatie-, recirculatie- en verwarmingsapparatuur (>50 cm | los)',                       'tarief' => 0.30,  'eenheid' => 'stuk' ],
                    'E4/15' => [ 'label' => 'Verwarmings- en warmwaterapparatuur (> 50 cm | inbouw)',                                   'tarief' => 0.20,  'eenheid' => 'kg'   ],
                    'E4/16' => [ 'label' => 'Grootkeukenapparatuur (ongekoeld)',                                                        'tarief' => 0.03,  'eenheid' => 'kg'   ],
                    'E4/17' => [ 'label' => 'Automaten (ongekoeld)',                                                                    'tarief' => 0.11,  'eenheid' => 'kg'   ],
                    'E4/18' => [ 'label' => 'Textielbewerkingsapparatuur (>50 cm)',                                                     'tarief' => 2.40,  'eenheid' => 'kg'   ],
                    'E4/19' => [ 'label' => 'Elektrische muziekinstrumenten (>50 cm)',                                                  'tarief' => 0.10,  'eenheid' => 'kg'   ],
                    'E4/20' => [ 'label' => 'Elektrisch speelgoed, ontspannings- en sportapparatuur (>50 cm)',                         'tarief' => 0.10,  'eenheid' => 'kg'   ],
                    'E4/21' => [ 'label' => 'Medische apparatuur (>50 cm)',                                                            'tarief' => 0.25,  'eenheid' => 'kg'   ],
                    'E4/22' => [ 'label' => 'Meet- en regelapparatuur (>50 cm)',                                                       'tarief' => 0.01,  'eenheid' => 'kg'   ],
                    'E4/23' => [ 'label' => 'Audio- en video-apparatuur (>50 cm)',                                                     'tarief' => 0.45,  'eenheid' => 'kg'   ],
                    'E4/24' => [ 'label' => 'I(C)T- en kantoorapparatuur (>50 cm | huishoudelijk)',                                    'tarief' => 0.07,  'eenheid' => 'kg'   ],
                    'E4/25' => [ 'label' => 'I(C)T- en kantoorapparatuur (>50 cm | professioneel)',                                    'tarief' => 0.02,  'eenheid' => 'kg'   ],
                    'E4/26' => [ 'label' => 'Armaturen voor TL-, spaar- en gasontladingslampen (>750 gram)',                           'tarief' => 0.19,  'eenheid' => 'stuk' ],
                    'E4/27' => [ 'label' => 'Armaturen met geïntegreerde LED (>750 gram)',                                             'tarief' => 0.19,  'eenheid' => 'stuk' ],
                    'E4/28' => [ 'label' => 'Armaturen voor verwisselbare LED (>750 gram)',                                            'tarief' => 0.19,  'eenheid' => 'stuk' ],
                    'E4/29' => [ 'label' => 'Elektrisch gereedschap (>50 cm)',                                                         'tarief' => 0.19,  'eenheid' => 'kg'   ],
                    'E4/32' => [ 'label' => 'Zonnepanelen',                                                                            'tarief' => 0.04,  'eenheid' => 'kg'   ],
                    'E4/33' => [ 'label' => 'Elektrische fietsen (zonder typegoedkeuring)',                                            'tarief' => 0.03,  'eenheid' => 'kg'   ],
                    'E4/34' => [ 'label' => 'Open Scope apparatuur (> 50 cm | zonder primaire elektrische functie)',                   'tarief' => 0.24,  'eenheid' => 'kg'   ],
                ],
            ],
            'EEE-05' => [
                'label' => 'Kleine apparatuur (zonder buitenafmeting van meer dan 50 cm)',
                'codes' => [
                    'E5/01' => [ 'label' => 'Barbecues, grill- en kookplaten (<= 50 cm)',                                                              'tarief' => 0.17, 'eenheid' => 'stuk' ],
                    'E5/02' => [ 'label' => 'Magnetrons en ovens (<= 50 cm)',                                                                          'tarief' => 0.17, 'eenheid' => 'stuk' ],
                    'E5/03' => [ 'label' => 'Huishoud-, keuken- en verzorgingsapparatuur (<= 50 cm)',                                                  'tarief' => 0.12, 'eenheid' => 'stuk' ],
                    'E5/04' => [ 'label' => 'Stofzuigers en vloerreinigers (<= 50 cm)',                                                                'tarief' => 2.23, 'eenheid' => 'stuk' ],
                    'E5/05' => [ 'label' => 'Ketels, boilers en geisers (<= 50 cm)',                                                                   'tarief' => 0.18, 'eenheid' => 'kg'   ],
                    'E5/06' => [ 'label' => 'Ventilatie-, recirculatie- en luchtbehandelingsinstallaties (<= 50 cm | <= 150 m3/uur)',                   'tarief' => 0.08, 'eenheid' => 'kg'   ],
                    'E5/07' => [ 'label' => 'Ventilatie-, recirculatie- en luchtbehandelingsinstallaties (<= 50 cm | > 150 m3/uur)',                    'tarief' => 0.08, 'eenheid' => 'kg'   ],
                    'E5/08' => [ 'label' => 'Ventilatie-, recirculatie- en verwarmingsapparatuur (<= 50 cm | los)',                                    'tarief' => 0.17, 'eenheid' => 'stuk' ],
                    'E5/09' => [ 'label' => 'Verwarmings- en warmwaterapparatuur (<=50 cm | inbouw)',                                                  'tarief' => 0.11, 'eenheid' => 'kg'   ],
                    'E5/10' => [ 'label' => 'Textielbewerkingsapparatuur (<= 50 cm)',                                                                  'tarief' => 1.00, 'eenheid' => 'kg'   ],
                    'E5/11' => [ 'label' => 'Elektrische muziekinstrumenten (<= 50 cm)',                                                               'tarief' => 0.64, 'eenheid' => 'kg'   ],
                    'E5/12' => [ 'label' => 'Spelcomputers',                                                                                           'tarief' => 0.02, 'eenheid' => 'kg'   ],
                    'E5/13' => [ 'label' => 'Elektrisch speelgoed, ontspannings- en sportapparatuur (<= 50 cm)',                                      'tarief' => 0.10, 'eenheid' => 'kg'   ],
                    'E5/14' => [ 'label' => 'Medische apparatuur (<= 50 cm)',                                                                          'tarief' => 0.18, 'eenheid' => 'kg'   ],
                    'E5/15' => [ 'label' => 'Meet- en regelapparatuur, incl. melders, sensoren en schakelaars (<= 50 cm)',                            'tarief' => 0.06, 'eenheid' => 'kg'   ],
                    'E5/16' => [ 'label' => 'Audio- en video-apparatuur (<= 50 cm)',                                                                   'tarief' => 0.45, 'eenheid' => 'kg'   ],
                    'E5/17' => [ 'label' => 'Armaturen voor TL-, spaar- en gasontladingslampen (<= 750 gram)',                                        'tarief' => 0.05, 'eenheid' => 'stuk' ],
                    'E5/18' => [ 'label' => 'Armaturen met geïntegreerde LED (<= 750 gram)',                                                           'tarief' => 0.05, 'eenheid' => 'stuk' ],
                    'E5/19' => [ 'label' => 'Armaturen voor verwisselbare LED (<= 750 gram)',                                                          'tarief' => 0.05, 'eenheid' => 'stuk' ],
                    'E5/20' => [ 'label' => 'Elektrisch gereedschap (<= 50 cm)',                                                                       'tarief' => 0.19, 'eenheid' => 'kg'   ],
                    'E5/22' => [ 'label' => 'Open Scope apparatuur (<= 50 cm | zonder primaire elektrische functie)',                                  'tarief' => 0.03, 'eenheid' => 'kg'   ],
                ],
            ],
            'EEE-06' => [
                'label' => 'Kleine IT- en telecommunicatieapparatuur (zonder buitenafmeting van meer dan 50 cm)',
                'codes' => [
                    'E6/01' => [ 'label' => 'Mobiele telefoons',                                          'tarief' => 0.07, 'eenheid' => 'kg' ],
                    'E6/02' => [ 'label' => 'Desktop computers (<= 50 cm)',                               'tarief' => 0.07, 'eenheid' => 'kg' ],
                    'E6/03' => [ 'label' => 'Printers en scanners (<= 50 cm)',                            'tarief' => 0.07, 'eenheid' => 'kg' ],
                    'E6/04' => [ 'label' => 'I(C)T- en kantoorapparatuur (<= 50 cm | huishoudelijk)',    'tarief' => 0.07, 'eenheid' => 'kg' ],
                    'E6/05' => [ 'label' => 'I(C)T- en kantoorapparatuur (<= 50 cm | professioneel)',    'tarief' => 0.02, 'eenheid' => 'kg' ],
                ],
            ],
        ];
    }

    /** Flat list: code => label */
    public static function get_flat(): array {
        $flat = [];
        foreach ( self::get_all() as $group ) {
            foreach ( $group['codes'] as $code => $data ) {
                $flat[ $code ] = $data['label'];
            }
        }
        return $flat;
    }

    /** Get single code data or null */
    public static function get_code( string $code ): ?array {
        foreach ( self::get_all() as $eee_code => $group ) {
            if ( isset( $group['codes'][ $code ] ) ) {
                $data              = $group['codes'][ $code ];
                $data['eee_code']  = $eee_code;
                $data['eee_label'] = $group['label'];
                $data['code']      = $code;
                return $data;
            }
        }
        return null;
    }
}

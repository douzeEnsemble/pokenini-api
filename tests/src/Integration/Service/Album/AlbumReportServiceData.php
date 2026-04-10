<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Album;

class AlbumReportServiceData
{
    /**
     * @return int[][]|string[][]|string[][][][]
     *
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    public static function getTypesReportFilteredProvider(): array
    {
        return [
            'primary_type' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'primary_types' => [
                        'grass',
                    ],
                ],
                6,
                0,
                0,
                0,
                6,
            ],
            'primary_type_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'primary_types' => [
                        'null',
                    ],
                ],
                1,
                0,
                0,
                0,
                1,
            ],
            'secondary_type' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'secondary_types' => [
                        'normal',
                    ],
                ],
                1,
                0,
                2,
                0,
                3,
            ],
            'secondary_type_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'secondary_types' => [
                        'null',
                    ],
                ],
                1,
                3,
                1,
                4,
                9,
            ],
            'primary_and_secondary_types' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'primary_types' => [
                        'bug',
                    ],
                    'secondary_types' => [
                        'flying',
                    ],
                ],
                1,
                0,
                0,
                2,
                3,
            ],
            'any_types' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'any_types' => [
                        'normal',
                    ],
                ],
                1,
                2,
                2,
                2,
                7,
            ],
            'any_types_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'any_types' => [
                        'null',
                    ],
                ],
                1,
                3,
                1,
                4,
                9,
            ],
        ];
    }

    /**
     * @return int[][]|string[][]|string[][][][]
     *
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    public static function getFormsReportFilteredProvider(): array
    {
        return [
            'category_form' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'category_forms' => [
                        'starter',
                    ],
                ],
                1,
                0,
                0,
                1,
                2,
            ],
            'category_form_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'category_forms' => [
                        'null',
                    ],
                ],
                8,
                3,
                3,
                6,
                20,
            ],
            'regional_form' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'regional_forms' => [
                        'alolan',
                    ],
                ],
                1,
                0,
                2,
                0,
                3,
            ],
            'regional_form_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'regional_forms' => [
                        'null',
                    ],
                ],
                8,
                3,
                1,
                7,
                19,
            ],
            'special_form' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'special_forms' => [
                        'gigantamax',
                    ],
                ],
                2,
                0,
                0,
                0,
                2,
            ],
            'special_form_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'special_forms' => [
                        'null',
                    ],
                ],
                5,
                3,
                3,
                7,
                18,
            ],
            'special_forms' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'special_forms' => [
                        'gigantamax',
                        'mega',
                    ],
                ],
                3,
                0,
                0,
                0,
                3,
            ],
            'variant_form' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'variant_forms' => [
                        'gender',
                    ],
                ],
                1,
                2,
                0,
                1,
                4,
            ],
            'variant_form_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'variant_forms' => [
                        'null',
                    ],
                ],
                8,
                1,
                3,
                6,
                18,
            ],
        ];
    }

    /**
     * @return int[][]|string[][]|string[][][][]
     */
    public static function getCatchStatesReportFilteredProvider(): array
    {
        return [
            'catch_state' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'catch_states' => [
                        'maybe',
                    ],
                ],
                0,
                3,
                0,
                0,
                3,
            ],
            'catch_state_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'catch_states' => [
                        'null',
                    ],
                ],
                1,
                0,
                0,
                0,
                1,
            ],
            'catch_states' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'catch_states' => [
                        'maybe',
                        'maybenot',
                    ],
                ],
                0,
                3,
                3,
                0,
                6,
            ],
            'catch_state_negative' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'catch_states' => [
                        '!maybe',
                    ],
                ],
                9,
                0,
                3,
                7,
                19,
            ],
        ];
    }

    /**
     * @return int[][]|string[][]|string[][][][]
     */
    public static function getOriginalGamesReportFilteredProvider(): array
    {
        return [
            'original_game_bundles' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'original_game_bundles' => [
                        'redgreenblueyellow',
                    ],
                ],
                4,
                1,
                1,
                6,
                12,
            ],
            'original_game_bundles_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'original_game_bundles' => [
                        'null',
                    ],
                ],
                0,
                0,
                0,
                0,
                0,
            ],
        ];
    }

    /**
     * @return int[][]|string[][]|string[][][][]
     */
    public static function getGamesBundlesReportFilteredProvider(): array
    {
        return [
            'game_bundle_availabilities' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'game_bundle_availabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                0,
                0,
                2,
                0,
                2,
            ],
            'game_bundle_availabilities_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'game_bundle_availabilities' => [
                        'null',
                    ],
                ],
                0,
                0,
                0,
                0,
                0,
            ],
            'game_bundle_availabilities_negative' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'game_bundle_availabilities' => [
                        '!ultrasunultramoon',
                    ],
                ],
                9,
                3,
                1,
                7,
                20,
            ],
            'game_bundle_shiny_availabilities' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'game_bundle_shiny_availabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                0,
                2,
                1,
                1,
                4,
            ],
            'game_bundle_shiny_availabilities_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'game_bundle_shiny_availabilities' => [
                        'null',
                    ],
                ],
                0,
                0,
                0,
                0,
                0,
            ],
            'game_bundle_shiny_availabilities_negative' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'game_bundle_shiny_availabilities' => [
                        '!ultrasunultramoon',
                    ],
                ],
                9,
                1,
                2,
                6,
                18,
            ],
        ];
    }

    /**
     * @return int[][]|string[][]|string[][][][]
     */
    public static function getFamiliesReportFilteredProvider(): array
    {
        return [
            'family' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'families' => [
                        'bulbasaur',
                    ],
                ],
                6,
                0,
                0,
                0,
                6,
            ],
            'family_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'families' => [
                        'null',
                    ],
                ],
                0,
                0,
                0,
                0,
                0,
            ],
            'families' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'families' => [
                        'bulbasaur',
                        'charmander',
                    ],
                ],
                6,
                0,
                0,
                3,
                9,
            ],
        ];
    }

    /**
     * @return int[][]|string[][]|string[][][][]
     */
    public static function getCollectionsReportFilteredProvider(): array
    {
        return [
            'collection_availalibities' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'collection_availabilities' => [
                        'pogoshadow',
                    ],
                ],
                1,
                0,
                0,
                0,
                1,
            ],
            'collection_availalibities_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'collection_availabilities' => [
                        'null',
                    ],
                ],
                0,
                0,
                0,
                0,
                0,
            ],
            'collections_availalibities' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'collection_availabilities' => [
                        'pogoshadow',
                        'pogoshadowshiny',
                    ],
                ],
                1,
                0,
                0,
                0,
                1,
            ],
            'collection_availalibities_negative' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'collection_availabilities' => [
                        '!pogoshadow',
                    ],
                ],
                8,
                3,
                3,
                7,
                21,
            ],
        ];
    }
}

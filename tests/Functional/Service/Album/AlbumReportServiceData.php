<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Album;

class AlbumReportServiceData
{
    /**
     * @return string[][]|string[][][][]|int[][]
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getTypesReportFilteredProvider(): array
    {
        return [
            'primary_type' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'primaryTypes' => [
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
                    'primaryTypes' => [
                        'null',
                    ],
                ],
                0,
                0,
                0,
                0,
                1,
            ],
            'secondary_type' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'secondaryTypes' => [
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
                    'secondaryTypes' => [
                        'null',
                    ],
                ],
                0,
                3,
                1,
                4,
                9,
            ],
            'primary_and_secondary_types' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'primaryTypes' => [
                        'bug',
                    ],
                    'secondaryTypes' => [
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
                    'anyTypes' => [
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
                    'anyTypes' => [
                        'null',
                    ],
                ],
                0,
                3,
                1,
                4,
                9,
            ],
        ];
    }

    /**
     * @return string[][]|string[][][][]|int[][]
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getFormsReportFilteredProvider(): array
    {
        return [
            'category_form' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'categoryForms' => [
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
                    'categoryForms' => [
                        'null',
                    ],
                ],
                7,
                3,
                3,
                6,
                20,
            ],
            'regional_form' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'regionalForms' => [
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
                    'regionalForms' => [
                        'null',
                    ],
                ],
                7,
                3,
                1,
                7,
                19,
            ],
            'special_form' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'specialForms' => [
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
                    'specialForms' => [
                        'null',
                    ],
                ],
                4,
                3,
                3,
                7,
                18,
            ],
            'special_forms' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'specialForms' => [
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
                    'variantForms' => [
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
                    'variantForms' => [
                        'null',
                    ],
                ],
                7,
                1,
                3,
                6,
                18,
            ],
        ];
    }

    /**
     * @return string[][]|string[][][][]|int[][]
     */
    public static function getCatchStatesReportFilteredProvider(): array
    {
        return [
            'catch_state' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'catchStates' => [
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
                    'catchStates' => [
                        'null',
                    ],
                ],
                0,
                0,
                0,
                0,
                1,
            ],
            'catch_states' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'catchStates' => [
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
        ];
    }

    /**
     * @return string[][]|string[][][][]|int[][]
     */
    public static function getGamesReportFilteredProvider(): array
    {
        return [
            'orignal_game_bundles' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'originalGameBundles' => [
                        'redgreenblueyellow',
                    ],
                ],
                3,
                1,
                1,
                6,
                12,
            ],
            'orignal_game_bundles_null' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'originalGameBundles' => [
                        'null',
                    ],
                ],
                0,
                0,
                0,
                0,
                0,
            ],
            'game_bundle_availabilities' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'gameBundleAvailabilities' => [
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
                    'gameBundleAvailabilities' => [
                        'null',
                    ],
                ],
                0,
                0,
                0,
                0,
                0,
            ],
            'game_bundle_shiny_availabilities' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'gameBundleShinyAvailabilities' => [
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
                    'gameBundleShinyAvailabilities' => [
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
     * @return string[][]|string[][][][]|int[][]
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
}

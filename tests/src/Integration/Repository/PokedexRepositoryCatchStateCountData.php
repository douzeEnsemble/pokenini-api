<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

class PokedexRepositoryCatchStateCountData
{
    /**
     * @return int[][][]|string[][][][]
     */
    public static function providerGetCatchStatesCountsTypesFilters(): array
    {
        return [
            'primary_type' => [
                [
                    'primary_types' => [
                        'grass',
                    ],
                ],
                [
                    6,
                    0,
                    0,
                    0,
                ],
            ],
            'primary_type_null' => [
                [
                    'primary_types' => [
                        'null',
                    ],
                ],
                [
                    1,
                    0,
                    0,
                    0,
                ],
            ],
            'secondary_type' => [
                [
                    'secondary_types' => [
                        'normal',
                    ],
                ],
                [
                    1,
                    0,
                    2,
                    0,
                ],
            ],
            'secondary_type_null' => [
                [
                    'secondary_types' => [
                        'null',
                    ],
                ],
                [
                    1,
                    3,
                    1,
                    4,
                ],
            ],
            'primary_and_secondary_types' => [
                [
                    'primary_types' => [
                        'bug',
                    ],
                    'secondary_types' => [
                        'flying',
                    ],
                ],
                [
                    1,
                    0,
                    0,
                    2,
                ],
            ],
            'any_types' => [
                [
                    'any_types' => [
                        'normal',
                    ],
                ],
                [
                    1,
                    2,
                    2,
                    2,
                ],
            ],
            'any_types_null' => [
                [
                    'any_types' => [
                        'null',
                    ],
                ],
                [
                    1,
                    3,
                    1,
                    4,
                ],
            ],
        ];
    }

    /**
     * @return int[][][]|string[][][][]
     *
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    public static function providerGetCatchStatesCountsFormsFilters(): array
    {
        return [
            'category_form' => [
                [
                    'category_forms' => [
                        'starter',
                    ],
                ],
                [
                    1,
                    0,
                    0,
                    1,
                ],
            ],
            'category_form_null' => [
                [
                    'category_forms' => [
                        'null',
                    ],
                ],
                [
                    8,
                    3,
                    3,
                    6,
                ],
            ],
            'regional_form' => [
                [
                    'regional_forms' => [
                        'alolan',
                    ],
                ],
                [
                    1,
                    0,
                    2,
                    0,
                ],
            ],
            'regional_form_null' => [
                [
                    'regional_forms' => [
                        'null',
                    ],
                ],
                [
                    8,
                    3,
                    1,
                    7,
                ],
            ],
            'special_form' => [
                [
                    'special_forms' => [
                        'gigantamax',
                    ],
                ],
                [
                    2,
                    0,
                    0,
                    0,
                ],
            ],
            'special_form_null' => [
                [
                    'special_forms' => [
                        'null',
                    ],
                ],
                [
                    5,
                    3,
                    3,
                    7,
                ],
            ],
            'special_forms' => [
                [
                    'special_forms' => [
                        'gigantamax',
                        'mega',
                    ],
                ],
                [
                    3,
                    0,
                    0,
                    0,
                ],
            ],
            'variant_form' => [
                [
                    'variant_forms' => [
                        'gender',
                    ],
                ],
                [
                    1,
                    2,
                    0,
                    1,
                ],
            ],
            'variant_form_null' => [
                [
                    'variant_forms' => [
                        'null',
                    ],
                ],
                [
                    8,
                    1,
                    3,
                    6,
                ],
            ],
        ];
    }

    /**
     * @return int[][][]|string[][][][]
     */
    public static function providerGetCatchStatesCountsCatchStatesFilters(): array
    {
        return [
            'catch_state' => [
                [
                    'catch_states' => [
                        'maybe',
                    ],
                ],
                [
                    0,
                    3,
                    0,
                    0,
                ],
            ],
            'catch_state_null' => [
                [
                    'catch_states' => [
                        'null',
                    ],
                ],
                [
                    1,
                    0,
                    0,
                    0,
                ],
            ],
            'catch_states' => [
                [
                    'catch_states' => [
                        'maybe',
                        'maybenot',
                    ],
                ],
                [
                    0,
                    3,
                    3,
                    0,
                ],
            ],
            'catch_state_negative' => [
                [
                    'catch_states' => [
                        '!maybe',
                    ],
                ],
                [
                    9,
                    0,
                    3,
                    7,
                ],
            ],
        ];
    }

    /**
     * @return int[][][]|string[][][][]
     */
    public static function providerGetCatchStatesCountsOriginalGamesFilters(): array
    {
        return [
            'original_game_bundle' => [
                [
                    'original_game_bundles' => [
                        'redgreenblueyellow',
                    ],
                ],
                [
                    4,
                    1,
                    1,
                    6,
                ],
            ],
            'original_game_bundle_null' => [
                [
                    'original_game_bundles' => [
                        'null',
                    ],
                ],
                [
                    0,
                    0,
                    0,
                    0,
                ],
            ],
        ];
    }

    /**
     * @return int[][][]|string[][][][]
     */
    public static function providerGetCatchStatesCountGamesBundlesFilters(): array
    {
        return [
            'game_bundle_availabilities' => [
                [
                    'game_bundle_availabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                [
                    0,
                    0,
                    2,
                    0,
                ],
            ],
            'game_bundle_availabilities_null' => [
                [
                    'game_bundle_availabilities' => [
                        'null',
                    ],
                ],
                [
                    0,
                    0,
                    0,
                    0,
                ],
            ],
            'game_bundle_availabilities_negative' => [
                [
                    'game_bundle_availabilities' => [
                        '!ultrasunultramoon',
                    ],
                ],
                [
                    9,
                    3,
                    1,
                    7,
                ],
            ],
            'game_bundle_shiny_availabilities' => [
                [
                    'game_bundle_shiny_availabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                [
                    0,
                    2,
                    1,
                    1,
                ],
            ],
            'game_bundle_shiny_availabilities_null' => [
                [
                    'game_bundle_shiny_availabilities' => [
                        'null',
                    ],
                ],
                [
                    0,
                    0,
                    0,
                    0,
                ],
            ],
            'game_bundle_shiny_availabilities_negative' => [
                [
                    'game_bundle_shiny_availabilities' => [
                        '!ultrasunultramoon',
                    ],
                ],
                [
                    9,
                    1,
                    2,
                    6,
                ],
            ],
        ];
    }

    /**
     * @return int[][][]|string[][][][]
     */
    public static function providerGetCatchStatesCountsFamiliesFilters(): array
    {
        return [
            'family' => [
                [
                    'families' => [
                        'bulbasaur',
                    ],
                ],
                [
                    6,
                    0,
                    0,
                    0,
                ],
            ],
            'family_null' => [
                [
                    'families' => [
                        'null',
                    ],
                ],
                [
                    0,
                    0,
                    0,
                    0,
                ],
            ],
            'families' => [
                [
                    'families' => [
                        'bulbasaur',
                        'charmander',
                    ],
                ],
                [
                    6,
                    0,
                    0,
                    3,
                ],
            ],
        ];
    }

    /**
     * @return int[][][]|string[][][][]
     */
    public static function providerGetCatchStatesCountCollectionsFilters(): array
    {
        return [
            'collection_availabilities' => [
                [
                    'collection_availabilities' => [
                        'pogoshadow',
                    ],
                ],
                [
                    1,
                    0,
                    0,
                    0,
                ],
            ],
            'collection_availabilities_null' => [
                [
                    'collection_availabilities' => [
                        'null',
                    ],
                ],
                [
                    0,
                    0,
                    0,
                    0,
                ],
            ],
            'collection_availabilities_negative' => [
                [
                    'collection_availabilities' => [
                        '!pogoshadow',
                    ],
                ],
                [
                    8,
                    3,
                    3,
                    7,
                ],
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

final class PokedexRepositoryCatchStateCountData
{
    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedCounts: array<int, int>,
     * }>
     */
    public static function providerGetCatchStatesCountsTypesFilters(): array
    {
        return [
            'primary_type' => [
                'filters' => [
                    'primary_types' => [
                        'grass',
                    ],
                ],
                'expectedCounts' => [
                    6,
                    0,
                    0,
                    0,
                ],
            ],
            'primary_type_null' => [
                'filters' => [
                    'primary_types' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    1,
                    0,
                    0,
                    0,
                ],
            ],
            'secondary_type' => [
                'filters' => [
                    'secondary_types' => [
                        'normal',
                    ],
                ],
                'expectedCounts' => [
                    1,
                    0,
                    2,
                    0,
                ],
            ],
            'secondary_type_null' => [
                'filters' => [
                    'secondary_types' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    1,
                    3,
                    1,
                    4,
                ],
            ],
            'primary_and_secondary_types' => [
                'filters' => [
                    'primary_types' => [
                        'bug',
                    ],
                    'secondary_types' => [
                        'flying',
                    ],
                ],
                'expectedCounts' => [
                    1,
                    0,
                    0,
                    2,
                ],
            ],
            'any_types' => [
                'filters' => [
                    'any_types' => [
                        'normal',
                    ],
                ],
                'expectedCounts' => [
                    1,
                    2,
                    2,
                    2,
                ],
            ],
            'any_types_null' => [
                'filters' => [
                    'any_types' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    1,
                    3,
                    1,
                    4,
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedCounts: array<int, int>,
     * }>
     *
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    public static function providerGetCatchStatesCountsFormsFilters(): array
    {
        return [
            'category_form' => [
                'filters' => [
                    'category_forms' => [
                        'starter',
                    ],
                ],
                'expectedCounts' => [
                    1,
                    0,
                    0,
                    1,
                ],
            ],
            'category_form_null' => [
                'filters' => [
                    'category_forms' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    8,
                    3,
                    3,
                    6,
                ],
            ],
            'regional_form' => [
                'filters' => [
                    'regional_forms' => [
                        'alolan',
                    ],
                ],
                'expectedCounts' => [
                    1,
                    0,
                    2,
                    0,
                ],
            ],
            'regional_form_null' => [
                'filters' => [
                    'regional_forms' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    8,
                    3,
                    1,
                    7,
                ],
            ],
            'special_form' => [
                'filters' => [
                    'special_forms' => [
                        'gigantamax',
                    ],
                ],
                'expectedCounts' => [
                    2,
                    0,
                    0,
                    0,
                ],
            ],
            'special_form_null' => [
                'filters' => [
                    'special_forms' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    5,
                    3,
                    3,
                    7,
                ],
            ],
            'special_forms' => [
                'filters' => [
                    'special_forms' => [
                        'gigantamax',
                        'mega',
                    ],
                ],
                'expectedCounts' => [
                    3,
                    0,
                    0,
                    0,
                ],
            ],
            'variant_form' => [
                'filters' => [
                    'variant_forms' => [
                        'gender',
                    ],
                ],
                'expectedCounts' => [
                    1,
                    2,
                    0,
                    1,
                ],
            ],
            'variant_form_null' => [
                'filters' => [
                    'variant_forms' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    8,
                    1,
                    3,
                    6,
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedCounts: array<int, int>,
     * }>
     */
    public static function providerGetCatchStatesCountsCatchStatesFilters(): array
    {
        return [
            'catch_state' => [
                'filters' => [
                    'catch_states' => [
                        'maybe',
                    ],
                ],
                'expectedCounts' => [
                    0,
                    3,
                    0,
                    0,
                ],
            ],
            'catch_state_null' => [
                'filters' => [
                    'catch_states' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    1,
                    0,
                    0,
                    0,
                ],
            ],
            'catch_states' => [
                'filters' => [
                    'catch_states' => [
                        'maybe',
                        'maybenot',
                    ],
                ],
                'expectedCounts' => [
                    0,
                    3,
                    3,
                    0,
                ],
            ],
            'catch_state_negative' => [
                'filters' => [
                    'catch_states' => [
                        '!maybe',
                    ],
                ],
                'expectedCounts' => [
                    9,
                    0,
                    3,
                    7,
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedCounts: array<int, int>,
     * }>
     */
    public static function providerGetCatchStatesCountsOriginalGamesFilters(): array
    {
        return [
            'original_game_bundle' => [
                'filters' => [
                    'original_game_bundles' => [
                        'redgreenblueyellow',
                    ],
                ],
                'expectedCounts' => [
                    4,
                    1,
                    1,
                    6,
                ],
            ],
            'original_game_bundle_null' => [
                'filters' => [
                    'original_game_bundles' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    0,
                    0,
                    0,
                    0,
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedCounts: array<int, int>,
     * }>
     */
    public static function providerGetCatchStatesCountGamesBundlesFilters(): array
    {
        return [
            'game_bundle_availabilities' => [
                'filters' => [
                    'game_bundle_availabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                'expectedCounts' => [
                    0,
                    0,
                    2,
                    0,
                ],
            ],
            'game_bundle_availabilities_null' => [
                'filters' => [
                    'game_bundle_availabilities' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    0,
                    0,
                    0,
                    0,
                ],
            ],
            'game_bundle_availabilities_negative' => [
                'filters' => [
                    'game_bundle_availabilities' => [
                        '!ultrasunultramoon',
                    ],
                ],
                'expectedCounts' => [
                    9,
                    3,
                    1,
                    7,
                ],
            ],
            'game_bundle_shiny_availabilities' => [
                'filters' => [
                    'game_bundle_shiny_availabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                'expectedCounts' => [
                    0,
                    2,
                    1,
                    1,
                ],
            ],
            'game_bundle_shiny_availabilities_null' => [
                'filters' => [
                    'game_bundle_shiny_availabilities' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    0,
                    0,
                    0,
                    0,
                ],
            ],
            'game_bundle_shiny_availabilities_negative' => [
                'filters' => [
                    'game_bundle_shiny_availabilities' => [
                        '!ultrasunultramoon',
                    ],
                ],
                'expectedCounts' => [
                    9,
                    1,
                    2,
                    6,
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedCounts: array<int, int>,
     * }>
     */
    public static function providerGetCatchStatesCountsFamiliesFilters(): array
    {
        return [
            'family' => [
                'filters' => [
                    'families' => [
                        'bulbasaur',
                    ],
                ],
                'expectedCounts' => [
                    6,
                    0,
                    0,
                    0,
                ],
            ],
            'family_null' => [
                'filters' => [
                    'families' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    0,
                    0,
                    0,
                    0,
                ],
            ],
            'families' => [
                'filters' => [
                    'families' => [
                        'bulbasaur',
                        'charmander',
                    ],
                ],
                'expectedCounts' => [
                    6,
                    0,
                    0,
                    3,
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedCounts: array<int, int>,
     * }>
     */
    public static function providerGetCatchStatesCountCollectionsFilters(): array
    {
        return [
            'collection_availabilities' => [
                'filters' => [
                    'collection_availabilities' => [
                        'pogoshadow',
                    ],
                ],
                'expectedCounts' => [
                    1,
                    0,
                    0,
                    0,
                ],
            ],
            'collection_availabilities_null' => [
                'filters' => [
                    'collection_availabilities' => [
                        'null',
                    ],
                ],
                'expectedCounts' => [
                    0,
                    0,
                    0,
                    0,
                ],
            ],
            'collection_availabilities_negative' => [
                'filters' => [
                    'collection_availabilities' => [
                        '!pogoshadow',
                    ],
                ],
                'expectedCounts' => [
                    8,
                    3,
                    3,
                    7,
                ],
            ],
        ];
    }
}

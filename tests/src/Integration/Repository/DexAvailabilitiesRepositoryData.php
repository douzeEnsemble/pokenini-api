<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

final class DexAvailabilitiesRepositoryData
{
    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedTotalCount: int,
     * }>
     */
    public static function providerGetTotalTypesFilters(): array
    {
        return [
            'primary_type' => [
                'filters' => [
                    'primary_types' => [
                        'grass',
                    ],
                ],
                'expectedTotalCount' => 6,
            ],
            'primary_type_null' => [
                'filters' => [
                    'primary_types' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 1,
            ],
            'secondary_type' => [
                'filters' => [
                    'secondary_types' => [
                        'normal',
                    ],
                ],
                'expectedTotalCount' => 3,
            ],
            'secondary_type_null' => [
                'filters' => [
                    'secondary_types' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 9,
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
                'expectedTotalCount' => 3,
            ],
            'any_type' => [
                'filters' => [
                    'any_types' => [
                        'normal',
                    ],
                ],
                'expectedTotalCount' => 7,
            ],
            'any_type_null' => [
                'filters' => [
                    'any_types' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 9,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedTotalCount: int,
     * }>
     */
    public static function providerGetTotalFormsFilters(): array
    {
        return [
            'category_form' => [
                'filters' => [
                    'category_forms' => [
                        'starter',
                    ],
                ],
                'expectedTotalCount' => 2,
            ],
            'category_form_null' => [
                'filters' => [
                    'category_forms' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 20,
            ],
            'regional_form' => [
                'filters' => [
                    'regional_forms' => [
                        'alolan',
                    ],
                ],
                'expectedTotalCount' => 3,
            ],
            'regional_form_null' => [
                'filters' => [
                    'regional_forms' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 19,
            ],
            'special_form' => [
                'filters' => [
                    'special_forms' => [
                        'gigantamax',
                    ],
                ],
                'expectedTotalCount' => 2,
            ],
            'special_form_null' => [
                'filters' => [
                    'special_forms' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 18,
            ],
            'special_forms' => [
                'filters' => [
                    'special_forms' => [
                        'gigantamax',
                        'mega',
                    ],
                ],
                'expectedTotalCount' => 3,
            ],
            'variant_form' => [
                'filters' => [
                    'variant_forms' => [
                        'gender',
                    ],
                ],
                'expectedTotalCount' => 4,
            ],
            'variant_form_null' => [
                'filters' => [
                    'variant_forms' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 18,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedTotalCount: int,
     * }>
     */
    public static function providerGetTotalCatchStatesFilters(): array
    {
        return [
            'catch_state' => [
                'filters' => [
                    'catch_states' => [
                        'maybe',
                    ],
                ],
                'expectedTotalCount' => 3,
            ],
            'catch_state_null' => [
                'filters' => [
                    'catch_states' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 1,
            ],
            'catch_states' => [
                'filters' => [
                    'catch_states' => [
                        'maybe',
                        'maybenot',
                    ],
                ],
                'expectedTotalCount' => 6,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedTotalCount: int,
     * }>
     */
    public static function providerGetTotalGamesFilters(): array
    {
        return [
            'original_game_bundles' => [
                'filters' => [
                    'original_game_bundles' => [
                        'ultrasunultramoon',
                    ],
                ],
                'expectedTotalCount' => 1,
            ],
            'original_game_bundles_null' => [
                'filters' => [
                    'original_game_bundles' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 0,
            ],
            'game_bundle_availabilities' => [
                'filters' => [
                    'game_bundle_availabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                'expectedTotalCount' => 2,
            ],
            'game_bundle_availabilities_null' => [
                'filters' => [
                    'game_bundle_availabilities' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 0,
            ],
            'game_bundle_shiny_availabilities' => [
                'filters' => [
                    'game_bundle_shiny_availabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                'expectedTotalCount' => 4,
            ],
            'game_bundle_shiny_availabilities_null' => [
                'filters' => [
                    'game_bundle_shiny_availabilities' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 0,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedTotalCount: int,
     * }>
     */
    public static function providerGetTotalCollectionsFilters(): array
    {
        return [
            'collection_availabilities' => [
                'filters' => [
                    'collection_availabilities' => [
                        'pogoshadow',
                    ],
                ],
                'expectedTotalCount' => 1,
            ],
            'collection_availabilities_null' => [
                'filters' => [
                    'collection_availabilities' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 0,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedTotalCount: int,
     * }>
     */
    public static function providerGetTotalFamiliesFilters(): array
    {
        return [
            'family' => [
                'filters' => [
                    'families' => [
                        'douze',
                    ],
                ],
                'expectedTotalCount' => 1,
            ],
            'family_null' => [
                'filters' => [
                    'families' => [
                        'null',
                    ],
                ],
                'expectedTotalCount' => 0,
            ],
            'families' => [
                'filters' => [
                    'families' => [
                        'douze',
                        'caterpie',
                    ],
                ],
                'expectedTotalCount' => 6,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedTotalCount: int,
     * }>
     */
    public static function providerGetTotalEmptyFilters(): array
    {
        return [
            'empty' => [
                'filters' => [
                    'primary_types' => [
                        '',
                    ],
                    'secondary_types' => [
                        '',
                    ],
                    'any_types' => [
                        '',
                    ],
                    'category_forms' => [
                        '',
                    ],
                    'regional_forms' => [
                        '',
                    ],
                    'special_forms' => [
                        '',
                    ],
                    'variant_forms' => [
                        '',
                    ],
                    'catch_states' => [
                        '',
                    ],
                    'original_game_bundles' => [
                        '',
                    ],
                    'game_bundle_availabilities' => [
                        '',
                    ],
                    'game_bundle_shiny_availabilities' => [
                        '',
                    ],
                    'families' => [
                        '',
                    ],
                ],
                'expectedTotalCount' => 22,
            ],
        ];
    }
}

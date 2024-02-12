<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

class DexAvailabilitiesRepositoryData
{
    /**
     * @return string[][][][]|int[][]
     */
    public static function providerGetTotalTypesFilters(): array
    {
        return [
            'primary_type' => [
                'filters' => [
                    'primaryTypes' => [
                        'grass',
                    ],
                ],
                'totalCount' => 6,
            ],
            'primary_type_null' => [
                'filters' => [
                    'primaryTypes' => [
                        'null',
                    ],
                ],
                'totalCount' => 1,
            ],
            'secondary_type' => [
                'filters' => [
                    'secondaryTypes' => [
                        'normal',
                    ],
                ],
                'totalCount' => 3,
            ],
            'secondary_type_null' => [
                'filters' => [
                    'secondaryTypes' => [
                        'null',
                    ],
                ],
                'totalCount' => 9,
            ],
            'primary_and_secondary_types' => [
                'filters' => [
                    'primaryTypes' => [
                        'bug',
                    ],
                    'secondaryTypes' => [
                        'flying',
                    ],
                ],
                'totalCount' => 3,
            ],
            'any_type' => [
                'filters' => [
                    'anyTypes' => [
                        'normal',
                    ],
                ],
                'totalCount' => 7,
            ],
        ];
    }

    /**
     * @return string[][][][]|int[][]
     */
    public static function providerGetTotalFormsFilters(): array
    {
        return [
            'category_form' => [
                'filters' => [
                    'categoryForms' => [
                        'starter',
                    ],
                ],
                'totalCount' => 2,
            ],
            'category_form_null' => [
                'filters' => [
                    'categoryForms' => [
                        'null',
                    ],
                ],
                'totalCount' => 20,
            ],
            'regional_form' => [
                'filters' => [
                    'regionalForms' => [
                        'alolan',
                    ],
                ],
                'totalCount' => 3,
            ],
            'regional_form_null' => [
                'filters' => [
                    'regionalForms' => [
                        'null',
                    ],
                ],
                'totalCount' => 19,
            ],
            'special_form' => [
                'filters' => [
                    'specialForms' => [
                        'gigantamax',
                    ],
                ],
                'totalCount' => 2,
            ],
            'special_form_null' => [
                'filters' => [
                    'specialForms' => [
                        'null',
                    ],
                ],
                'totalCount' => 18,
            ],
            'special_forms' => [
                'filters' => [
                    'specialForms' => [
                        'gigantamax',
                        'mega',
                    ],
                ],
                'totalCount' => 3,
            ],
            'variant_form' => [
                'filters' => [
                    'variantForms' => [
                        'gender',
                    ],
                ],
                'totalCount' => 4,
            ],
            'variant_form_null' => [
                'filters' => [
                    'variantForms' => [
                        'null',
                    ],
                ],
                'totalCount' => 18,
            ],
        ];
    }

    /**
     * @return string[][][][]|int[][]
     */
    public static function providerGetTotalCatchStatesFilters(): array
    {
        return [
            'catch_state' => [
                'filters' => [
                    'catchStates' => [
                        'maybe',
                    ],
                ],
                'totalCount' => 3,
            ],
            'catch_state_null' => [
                'filters' => [
                    'catchStates' => [
                        'null',
                    ],
                ],
                'totalCount' => 1,
            ],
            'catch_states' => [
                'filters' => [
                    'catchStates' => [
                        'maybe',
                        'maybenot',
                    ],
                ],
                'totalCount' => 6,
            ],
        ];
    }

    /**
     * @return string[][][][]|int[][]
     */
    public static function providerGetTotalGamesFilters(): array
    {
        return [
            'original_game_bundles' => [
                'filters' => [
                    'originalGameBundles' => [
                        'ultrasunultramoon',
                    ],
                ],
                'totalCount' => 1,
            ],
            'original_game_bundles_null' => [
                'filters' => [
                    'originalGameBundles' => [
                        'null',
                    ],
                ],
                'totalCount' => 0,
            ],
            'game_bundle_availabilities' => [
                'filters' => [
                    'gameBundleAvailabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                'totalCount' => 2,
            ],
            'game_bundle_availabilities_null' => [
                'filters' => [
                    'gameBundleAvailabilities' => [
                        'null',
                    ],
                ],
                'totalCount' => 0,
            ],
            'game_bundle_shiny_availabilities' => [
                'filters' => [
                    'gameBundleShinyAvailabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                'totalCount' => 4,
            ],
            'game_bundle_shiny_availabilities_null' => [
                'filters' => [
                    'gameBundleShinyAvailabilities' => [
                        'null',
                    ],
                ],
                'totalCount' => 0,
            ],
        ];
    }

    /**
     * @return string[][][][]|int[][]
     */
    public static function providerGetTotalEmptyFilters(): array
    {
        return [
            'empty' => [
                'filters' => [
                    'primaryTypes' => [
                        '',
                    ],
                    'secondaryTypes' => [
                        '',
                    ],
                    'anyTypes' => [
                        '',
                    ],
                    'categoryForms' => [
                        '',
                    ],
                    'regionalForms' => [
                        '',
                    ],
                    'specialForms' => [
                        '',
                    ],
                    'variantForms' => [
                        '',
                    ],
                    'catchStates' => [
                        '',
                    ],
                ],
                'totalCount' => 22,
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service\Album;

final class AlbumReportServiceData
{
    /**
     * @return array<string, array{
     *  trainerId: string,
     *  dexSlug: string,
     *  filters: array<string, array<int, string>>,
     *  countNo: int,
     *  countMaybe: int,
     *  countMaybeNot: int,
     *  countYes: int,
     *  countTotal: int,
     * }>
     *
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    public static function getTypesReportFilteredProvider(): array
    {
        return [
            'primary_type' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'primary_types' => [
                        'grass',
                    ],
                ],
                'countNo' => 6,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 6,
            ],
            'primary_type_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'primary_types' => [
                        'null',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 1,
            ],
            'secondary_type' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'secondary_types' => [
                        'normal',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 0,
                'countMaybeNot' => 2,
                'countYes' => 0,
                'countTotal' => 3,
            ],
            'secondary_type_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'secondary_types' => [
                        'null',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 3,
                'countMaybeNot' => 1,
                'countYes' => 4,
                'countTotal' => 9,
            ],
            'primary_and_secondary_types' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'primary_types' => [
                        'bug',
                    ],
                    'secondary_types' => [
                        'flying',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 2,
                'countTotal' => 3,
            ],
            'any_types' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'any_types' => [
                        'normal',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 2,
                'countMaybeNot' => 2,
                'countYes' => 2,
                'countTotal' => 7,
            ],
            'any_types_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'any_types' => [
                        'null',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 3,
                'countMaybeNot' => 1,
                'countYes' => 4,
                'countTotal' => 9,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  trainerId: string,
     *  dexSlug: string,
     *  filters: array<string, array<int, string>>,
     *  countNo: int,
     *  countMaybe: int,
     *  countMaybeNot: int,
     *  countYes: int,
     *  countTotal: int,
     * }>
     *
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    public static function getFormsReportFilteredProvider(): array
    {
        return [
            'category_form' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'category_forms' => [
                        'starter',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 1,
                'countTotal' => 2,
            ],
            'category_form_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'category_forms' => [
                        'null',
                    ],
                ],
                'countNo' => 8,
                'countMaybe' => 3,
                'countMaybeNot' => 3,
                'countYes' => 6,
                'countTotal' => 20,
            ],
            'regional_form' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'regional_forms' => [
                        'alolan',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 0,
                'countMaybeNot' => 2,
                'countYes' => 0,
                'countTotal' => 3,
            ],
            'regional_form_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'regional_forms' => [
                        'null',
                    ],
                ],
                'countNo' => 8,
                'countMaybe' => 3,
                'countMaybeNot' => 1,
                'countYes' => 7,
                'countTotal' => 19,
            ],
            'special_form' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'special_forms' => [
                        'gigantamax',
                    ],
                ],
                'countNo' => 2,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 2,
            ],
            'special_form_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'special_forms' => [
                        'null',
                    ],
                ],
                'countNo' => 5,
                'countMaybe' => 3,
                'countMaybeNot' => 3,
                'countYes' => 7,
                'countTotal' => 18,
            ],
            'special_forms' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'special_forms' => [
                        'gigantamax',
                        'mega',
                    ],
                ],
                'countNo' => 3,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 3,
            ],
            'variant_form' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'variant_forms' => [
                        'gender',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 2,
                'countMaybeNot' => 0,
                'countYes' => 1,
                'countTotal' => 4,
            ],
            'variant_form_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'variant_forms' => [
                        'null',
                    ],
                ],
                'countNo' => 8,
                'countMaybe' => 1,
                'countMaybeNot' => 3,
                'countYes' => 6,
                'countTotal' => 18,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  trainerId: string,
     *  dexSlug: string,
     *  filters: array<string, array<int, string>>,
     *  countNo: int,
     *  countMaybe: int,
     *  countMaybeNot: int,
     *  countYes: int,
     *  countTotal: int,
     * }>
     */
    public static function getCatchStatesReportFilteredProvider(): array
    {
        return [
            'catch_state' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'catch_states' => [
                        'maybe',
                    ],
                ],
                'countNo' => 0,
                'countMaybe' => 3,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 3,
            ],
            'catch_state_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'catch_states' => [
                        'null',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 1,
            ],
            'catch_states' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'catch_states' => [
                        'maybe',
                        'maybenot',
                    ],
                ],
                'countNo' => 0,
                'countMaybe' => 3,
                'countMaybeNot' => 3,
                'countYes' => 0,
                'countTotal' => 6,
            ],
            'catch_state_negative' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'catch_states' => [
                        '!maybe',
                    ],
                ],
                'countNo' => 9,
                'countMaybe' => 0,
                'countMaybeNot' => 3,
                'countYes' => 7,
                'countTotal' => 19,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  trainerId: string,
     *  dexSlug: string,
     *  filters: array<string, array<int, string>>,
     *  countNo: int,
     *  countMaybe: int,
     *  countMaybeNot: int,
     *  countYes: int,
     *  countTotal: int,
     * }>
     */
    public static function getOriginalGamesReportFilteredProvider(): array
    {
        return [
            'original_game_bundles' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'original_game_bundles' => [
                        'redgreenblueyellow',
                    ],
                ],
                'countNo' => 4,
                'countMaybe' => 1,
                'countMaybeNot' => 1,
                'countYes' => 6,
                'countTotal' => 12,
            ],
            'original_game_bundles_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'original_game_bundles' => [
                        'null',
                    ],
                ],
                'countNo' => 0,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 0,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  trainerId: string,
     *  dexSlug: string,
     *  filters: array<string, array<int, string>>,
     *  countNo: int,
     *  countMaybe: int,
     *  countMaybeNot: int,
     *  countYes: int,
     *  countTotal: int,
     * }>
     */
    public static function getGamesBundlesReportFilteredProvider(): array
    {
        return [
            'game_bundle_availabilities' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'game_bundle_availabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                'countNo' => 0,
                'countMaybe' => 0,
                'countMaybeNot' => 2,
                'countYes' => 0,
                'countTotal' => 2,
            ],
            'game_bundle_availabilities_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'game_bundle_availabilities' => [
                        'null',
                    ],
                ],
                'countNo' => 0,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 0,
            ],
            'game_bundle_availabilities_negative' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'game_bundle_availabilities' => [
                        '!ultrasunultramoon',
                    ],
                ],
                'countNo' => 9,
                'countMaybe' => 3,
                'countMaybeNot' => 1,
                'countYes' => 7,
                'countTotal' => 20,
            ],
            'game_bundle_shiny_availabilities' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'game_bundle_shiny_availabilities' => [
                        'ultrasunultramoon',
                    ],
                ],
                'countNo' => 0,
                'countMaybe' => 2,
                'countMaybeNot' => 1,
                'countYes' => 1,
                'countTotal' => 4,
            ],
            'game_bundle_shiny_availabilities_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'game_bundle_shiny_availabilities' => [
                        'null',
                    ],
                ],
                'countNo' => 0,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 0,
            ],
            'game_bundle_shiny_availabilities_negative' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'game_bundle_shiny_availabilities' => [
                        '!ultrasunultramoon',
                    ],
                ],
                'countNo' => 9,
                'countMaybe' => 1,
                'countMaybeNot' => 2,
                'countYes' => 6,
                'countTotal' => 18,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  trainerId: string,
     *  dexSlug: string,
     *  filters: array<string, array<int, string>>,
     *  countNo: int,
     *  countMaybe: int,
     *  countMaybeNot: int,
     *  countYes: int,
     *  countTotal: int,
     * }>
     */
    public static function getFamiliesReportFilteredProvider(): array
    {
        return [
            'family' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'families' => [
                        'bulbasaur',
                    ],
                ],
                'countNo' => 6,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 6,
            ],
            'family_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'families' => [
                        'null',
                    ],
                ],
                'countNo' => 0,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 0,
            ],
            'families' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'families' => [
                        'bulbasaur',
                        'charmander',
                    ],
                ],
                'countNo' => 6,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 3,
                'countTotal' => 9,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *  trainerId: string,
     *  dexSlug: string,
     *  filters: array<string, array<int, string>>,
     *  countNo: int,
     *  countMaybe: int,
     *  countMaybeNot: int,
     *  countYes: int,
     *  countTotal: int,
     * }>
     */
    public static function getCollectionsReportFilteredProvider(): array
    {
        return [
            'collection_availalibities' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'collection_availabilities' => [
                        'pogoshadow',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 1,
            ],
            'collection_availalibities_null' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'collection_availabilities' => [
                        'null',
                    ],
                ],
                'countNo' => 0,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 0,
            ],
            'collections_availalibities' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'collection_availabilities' => [
                        'pogoshadow',
                        'pogoshadowshiny',
                    ],
                ],
                'countNo' => 1,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 1,
            ],
            'collection_availalibities_negative' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'filters' => [
                    'collection_availabilities' => [
                        '!pogoshadow',
                    ],
                ],
                'countNo' => 8,
                'countMaybe' => 3,
                'countMaybeNot' => 3,
                'countYes' => 7,
                'countTotal' => 21,
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Common\Data;

/**
 * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
 */
final class AlbumData
{
    /**
     * @return int[][]|null[][]|string[][]|string[][][]
     */
    public static function getExpectedRegGreenBlueYellowContent(
        ?string $bulbasaurCatchState,
        ?string $ivysaurCatchState,
        ?string $venusaurCatchState,
        ?string $caterpieCatchState,
        ?string $metapodCatchState,
        ?string $butterfreeCatchState,
        ?string $douzeCatchState,
    ): array {
        $bulbasaurData = array_merge(
            PokemonData::getBulbasaurData(),
            [
                'pokemon_regional_dex_number' => 1,
                'pokemon_order_number' => '0001-0001-000',
            ],
            self::getCatchStateDataFromSlug($bulbasaurCatchState)
        );

        $ivysaurData = array_merge(
            PokemonData::getIvysaurData(),
            [
                'pokemon_regional_dex_number' => 2,
                'pokemon_order_number' => '0002-0002-001',
            ],
            self::getCatchStateDataFromSlug($ivysaurCatchState)
        );

        $venusaurData = array_merge(
            PokemonData::getVenusaurData(),
            [
                'pokemon_regional_dex_number' => 3,
                'pokemon_order_number' => '0003-0003-002',
            ],
            self::getCatchStateDataFromSlug($venusaurCatchState)
        );

        $caterpieData = array_merge(
            PokemonData::getCaterpieData(),
            self::getCatchStateDataFromSlug($caterpieCatchState)
        );

        $metapodData = array_merge(
            PokemonData::getMetapodData(),
            self::getCatchStateDataFromSlug($metapodCatchState)
        );

        $butterfreeData = array_merge(
            PokemonData::getButterfreeData(),
            self::getCatchStateDataFromSlug($butterfreeCatchState)
        );

        $douzeData = array_merge(
            PokemonData::getDouzeData(),
            self::getCatchStateDataFromSlug($douzeCatchState)
        );

        return [
            $bulbasaurData,
            $ivysaurData,
            $venusaurData,
            $caterpieData,
            $metapodData,
            $butterfreeData,
            $douzeData,
        ];
    }

    /**
     * @return int[][]|null[][]|string[][]|string[][][]
     */
    public static function getExpectedGoldSilverCrystalContent(
        ?string $bulbasaurCatchState,
        ?string $ivysaurCatchState,
        ?string $venusaurCatchState,
        ?string $charmanderCatchState,
        ?string $charmeleonCatchState,
        ?string $charizardCatchState,
        ?string $caterpieCatchState,
        ?string $metapodCatchState,
        ?string $butterfreeCatchState,
    ): array {
        $bulbasaurData = array_merge(
            PokemonData::getBulbasaurData(),
            [
                'pokemon_regional_dex_number' => 231,
                'pokemon_order_number' => '0231-0001-000',
            ],
            self::getCatchStateDataFromSlug($bulbasaurCatchState)
        );

        $ivysaurData = array_merge(
            PokemonData::getIvysaurData(),
            [
                'pokemon_regional_dex_number' => 232,
                'pokemon_order_number' => '0232-0002-001',
            ],
            self::getCatchStateDataFromSlug($ivysaurCatchState)
        );

        $venusaurData = array_merge(
            PokemonData::getVenusaurData(),
            [
                'pokemon_regional_dex_number' => 233,
                'pokemon_order_number' => '0233-0003-002',
            ],
            self::getCatchStateDataFromSlug($venusaurCatchState)
        );

        $charmanderData = array_merge(
            PokemonData::getCharmanderData(),
            self::getCatchStateDataFromSlug($charmanderCatchState)
        );

        $charmeleonData = array_merge(
            PokemonData::getCharmeleonData(),
            self::getCatchStateDataFromSlug($charmeleonCatchState)
        );

        $charizardData = array_merge(
            PokemonData::getCharizardData(),
            self::getCatchStateDataFromSlug($charizardCatchState)
        );

        $caterpieData = array_merge(
            PokemonData::getCaterpieData(),
            [
                'pokemon_regional_dex_number' => 24,
                'pokemon_order_number' => '0024-0010-000',
            ],
            self::getCatchStateDataFromSlug($caterpieCatchState)
        );

        $metapodData = array_merge(
            PokemonData::getMetapodData(),
            [
                'pokemon_regional_dex_number' => 25,
                'pokemon_order_number' => '0025-0011-001',
            ],
            self::getCatchStateDataFromSlug($metapodCatchState)
        );

        $butterfreeData = array_merge(
            PokemonData::getButterfreeData(),
            [
                'pokemon_regional_dex_number' => 26,
                'pokemon_order_number' => '0026-0012-002',
            ],
            self::getCatchStateDataFromSlug($butterfreeCatchState)
        );

        return [
            $caterpieData,
            $metapodData,
            $butterfreeData,
            $bulbasaurData,
            $ivysaurData,
            $venusaurData,
            $charmanderData,
            $charmeleonData,
            $charizardData,
        ];
    }

    /**
     * @return int[][]|null[][]|string[][]|string[][][]
     */
    public static function getExpectedHomeContent(): array
    {
        return [
            array_merge(
                PokemonData::getBulbasaurData(),
                self::getCatchStateDataFromSlug('no')
            ),
            array_merge(
                PokemonData::getIvysaurData(),
                self::getCatchStateDataFromSlug('no')
            ),
            array_merge(
                PokemonData::getVenusaurData(),
                self::getCatchStateDataFromSlug('no')
            ),
            [
                'pokemon_national_dex_number' => 3,
                'pokemon_regional_dex_number' => null,
                'pokemon_order_number' => '9999-0003-003',
                'pokemon_slug' => 'venusaur-f',
                'pokemon_name' => 'Venusaur ♀',
                'pokemon_simplified_name' => 'Venusaur',
                'pokemon_forms_label' => '♀️',
                'pokemon_french_name' => 'Florizarre ♀',
                'pokemon_simplified_french_name' => 'Florizarre',
                'pokemon_forms_french_label' => '♀️',
                'pokemon_icon' => 'venusaur',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => 'gender',
                'variant_form_name' => 'Gender',
                'catch_state_slug' => 'no',
                'catch_state_name' => 'No',
                'catch_state_french_name' => 'Non',
                'family_lead_slug' => 'bulbasaur',
                'pokemon_family_order' => 3,
                'primary_type_slug' => 'grass',
                'primary_type_name' => 'Grass',
                'primary_type_french_name' => 'Plante',
                'secondary_type_slug' => 'poison',
                'secondary_type_name' => 'Poison',
                'secondary_type_french_name' => 'Poison',
                'original_game_bundle_slug' => 'diamondpearlplatinium',
                'game_bundles' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
                'game_bundles_shiny' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
            ],
            [
                'pokemon_national_dex_number' => 3,
                'pokemon_regional_dex_number' => null,
                'pokemon_order_number' => '9999-0003-004',
                'pokemon_slug' => 'venusaur-mega',
                'pokemon_name' => 'Mega Venusaur',
                'pokemon_simplified_name' => 'Venusaur',
                'pokemon_forms_label' => 'Mega',
                'pokemon_french_name' => 'Mega Florizarre',
                'pokemon_simplified_french_name' => 'Florizarre',
                'pokemon_forms_french_label' => 'Mega',
                'pokemon_icon' => 'venusaur',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => 'mega',
                'special_form_name' => 'Mega',
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => 'no',
                'catch_state_name' => 'No',
                'catch_state_french_name' => 'Non',
                'family_lead_slug' => 'bulbasaur',
                'pokemon_family_order' => 4,
                'primary_type_slug' => 'grass',
                'primary_type_name' => 'Grass',
                'primary_type_french_name' => 'Plante',
                'secondary_type_slug' => 'poison',
                'secondary_type_name' => 'Poison',
                'secondary_type_french_name' => 'Poison',
                'original_game_bundle_slug' => 'xy',
                'game_bundles' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
                'game_bundles_shiny' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
            ],
            [
                'pokemon_national_dex_number' => 3,
                'pokemon_regional_dex_number' => null,
                'pokemon_order_number' => '9999-0003-005',
                'pokemon_slug' => 'venusaur-gmax',
                'pokemon_name' => 'Gigantamax Venusaur',
                'pokemon_simplified_name' => 'Venusaur',
                'pokemon_forms_label' => 'Gigantamax',
                'pokemon_french_name' => 'Gigamax Florizarre',
                'pokemon_simplified_french_name' => 'Florizarre',
                'pokemon_forms_french_label' => 'Gigamax',
                'pokemon_icon' => 'venusaur',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => 'gigantamax',
                'special_form_name' => 'Gigantamax',
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => 'no',
                'catch_state_name' => 'No',
                'catch_state_french_name' => 'Non',
                'family_lead_slug' => 'bulbasaur',
                'pokemon_family_order' => 5,
                'primary_type_slug' => 'grass',
                'primary_type_name' => 'Grass',
                'primary_type_french_name' => 'Plante',
                'secondary_type_slug' => 'poison',
                'secondary_type_name' => 'Poison',
                'secondary_type_french_name' => 'Poison',
                'original_game_bundle_slug' => 'swordshield',
                'game_bundles' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
                'game_bundles_shiny' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
            ],
            array_merge(
                PokemonData::getCharmanderData(),
                self::getCatchStateDataFromSlug('yes')
            ),
            array_merge(
                PokemonData::getCharmeleonData(),
                self::getCatchStateDataFromSlug('yes')
            ),
            array_merge(
                PokemonData::getCharizardData(),
                self::getCatchStateDataFromSlug('yes')
            ),
            array_merge(
                PokemonData::getCaterpieData(),
                self::getCatchStateDataFromSlug('maybe')
            ),
            array_merge(
                PokemonData::getMetapodData(),
                self::getCatchStateDataFromSlug('maybenot')
            ),
            array_merge(
                PokemonData::getButterfreeData(),
                self::getCatchStateDataFromSlug('yes')
            ),
            [
                'pokemon_national_dex_number' => 12,
                'pokemon_regional_dex_number' => null,
                'pokemon_order_number' => '9999-0012-003',
                'pokemon_slug' => 'butterfree-f',
                'pokemon_name' => 'Butterfree ♀',
                'pokemon_simplified_name' => 'Butterfree',
                'pokemon_forms_label' => '♀️',
                'pokemon_french_name' => 'Papilusion',
                'pokemon_simplified_french_name' => 'Papilusion',
                'pokemon_forms_french_label' => '♀️',
                'pokemon_icon' => 'butterfree',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => 'gender',
                'variant_form_name' => 'Gender',
                'catch_state_slug' => 'yes',
                'catch_state_name' => 'Yes',
                'catch_state_french_name' => 'Oui',
                'family_lead_slug' => 'caterpie',
                'pokemon_family_order' => 3,
                'primary_type_slug' => 'bug',
                'primary_type_name' => 'Bug',
                'primary_type_french_name' => 'Insecte',
                'secondary_type_slug' => 'flying',
                'secondary_type_name' => 'Flying',
                'secondary_type_french_name' => 'Vol',
                'original_game_bundle_slug' => 'diamondpearlplatinium',
                'game_bundles' => [],
                'game_bundles_shiny' => [],
            ],
            [
                'pokemon_national_dex_number' => 12,
                'pokemon_regional_dex_number' => null,
                'pokemon_order_number' => '9999-0012-004',
                'pokemon_slug' => 'butterfree-gmax',
                'pokemon_name' => 'Gigantamax Butterfree',
                'pokemon_simplified_name' => 'Butterfree',
                'pokemon_forms_label' => 'Gigantamax',
                'pokemon_french_name' => 'Gigamax Papilusion',
                'pokemon_simplified_french_name' => 'Papilusion',
                'pokemon_forms_french_label' => 'Gigamax',
                'pokemon_icon' => 'butterfree-gmax',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => 'gigantamax',
                'special_form_name' => 'Gigantamax',
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => 'no',
                'catch_state_name' => 'No',
                'catch_state_french_name' => 'Non',
                'family_lead_slug' => 'caterpie',
                'pokemon_family_order' => 4,
                'primary_type_slug' => 'bug',
                'primary_type_name' => 'Bug',
                'primary_type_french_name' => 'Insecte',
                'secondary_type_slug' => 'flying',
                'secondary_type_name' => 'Flying',
                'secondary_type_french_name' => 'Vol',
                'original_game_bundle_slug' => 'swordshield',
                'game_bundles' => [],
                'game_bundles_shiny' => [],
            ],
            array_merge(
                PokemonData::getRattataData(),
                self::getCatchStateDataFromSlug('yes')
            ),
            array_merge(
                PokemonData::getRattataFemaleData(),
                self::getCatchStateDataFromSlug('maybe')
            ),
            array_merge(
                PokemonData::getRattataAlolanData(),
                self::getCatchStateDataFromSlug('maybenot')
            ),
            array_merge(
                PokemonData::getRaticateData(),
                self::getCatchStateDataFromSlug('yes')
            ),
            array_merge(
                PokemonData::getRaticateFemaleData(),
                self::getCatchStateDataFromSlug('maybe')
            ),
            array_merge(
                PokemonData::getRaticateAlolanData(),
                self::getCatchStateDataFromSlug('maybenot')
            ),
            array_merge(
                PokemonData::getRaticateAlolanTotemData(),
                self::getCatchStateDataFromSlug('no')
            ),
            PokemonData::getDouzeData(),
        ];
    }

    /**
     * @return int[][]|null[][]|string[][]|string[][][]
     */
    public static function getExpectedHomeShinyContent(): array
    {
        return [
            array_merge(
                PokemonData::getBulbasaurData(),
                self::getCatchStateDataFromSlug(null)
            ),
            array_merge(
                PokemonData::getIvysaurData(),
                self::getCatchStateDataFromSlug(null)
            ),
            array_merge(
                PokemonData::getVenusaurData(),
                self::getCatchStateDataFromSlug(null)
            ),
            [
                'pokemon_national_dex_number' => 3,
                'pokemon_regional_dex_number' => null,
                'pokemon_order_number' => '9999-0003-003',
                'pokemon_slug' => 'venusaur-f',
                'pokemon_name' => 'Venusaur ♀',
                'pokemon_simplified_name' => 'Venusaur',
                'pokemon_forms_label' => '♀️',
                'pokemon_french_name' => 'Florizarre ♀',
                'pokemon_simplified_french_name' => 'Florizarre',
                'pokemon_forms_french_label' => '♀️',
                'pokemon_icon' => 'venusaur',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => 'gender',
                'variant_form_name' => 'Gender',
                'catch_state_slug' => null,
                'catch_state_name' => null,
                'catch_state_french_name' => null,
                'family_lead_slug' => 'bulbasaur',
                'pokemon_family_order' => 3,
                'primary_type_slug' => 'grass',
                'primary_type_name' => 'Grass',
                'primary_type_french_name' => 'Plante',
                'secondary_type_slug' => 'poison',
                'secondary_type_name' => 'Poison',
                'secondary_type_french_name' => 'Poison',
                'original_game_bundle_slug' => 'diamondpearlplatinium',
                'game_bundles' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
                'game_bundles_shiny' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
            ],
            [
                'pokemon_national_dex_number' => 3,
                'pokemon_regional_dex_number' => null,
                'pokemon_order_number' => '9999-0003-004',
                'pokemon_slug' => 'venusaur-mega',
                'pokemon_name' => 'Mega Venusaur',
                'pokemon_simplified_name' => 'Venusaur',
                'pokemon_forms_label' => 'Mega',
                'pokemon_french_name' => 'Mega Florizarre',
                'pokemon_simplified_french_name' => 'Florizarre',
                'pokemon_forms_french_label' => 'Mega',
                'pokemon_icon' => 'venusaur',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => 'mega',
                'special_form_name' => 'Mega',
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => null,
                'catch_state_name' => null,
                'catch_state_french_name' => null,
                'family_lead_slug' => 'bulbasaur',
                'pokemon_family_order' => 4,
                'primary_type_slug' => 'grass',
                'primary_type_name' => 'Grass',
                'primary_type_french_name' => 'Plante',
                'secondary_type_slug' => 'poison',
                'secondary_type_name' => 'Poison',
                'secondary_type_french_name' => 'Poison',
                'original_game_bundle_slug' => 'xy',
                'game_bundles' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
                'game_bundles_shiny' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
            ],
            [
                'pokemon_national_dex_number' => 3,
                'pokemon_regional_dex_number' => null,
                'pokemon_order_number' => '9999-0003-005',
                'pokemon_slug' => 'venusaur-gmax',
                'pokemon_name' => 'Gigantamax Venusaur',
                'pokemon_simplified_name' => 'Venusaur',
                'pokemon_forms_label' => 'Gigantamax',
                'pokemon_french_name' => 'Gigamax Florizarre',
                'pokemon_simplified_french_name' => 'Florizarre',
                'pokemon_forms_french_label' => 'Gigamax',
                'pokemon_icon' => 'venusaur',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => 'gigantamax',
                'special_form_name' => 'Gigantamax',
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => null,
                'catch_state_name' => null,
                'catch_state_french_name' => null,
                'family_lead_slug' => 'bulbasaur',
                'pokemon_family_order' => 5,
                'primary_type_slug' => 'grass',
                'primary_type_name' => 'Grass',
                'primary_type_french_name' => 'Plante',
                'secondary_type_slug' => 'poison',
                'secondary_type_name' => 'Poison',
                'secondary_type_french_name' => 'Poison',
                'original_game_bundle_slug' => 'swordshield',
                'game_bundles' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
                'game_bundles_shiny' => [
                    'redgreenblueyellow',
                    'goldsilvercrystal',
                ],
            ],
            array_merge(
                PokemonData::getCaterpieData(),
                self::getCatchStateDataFromSlug(null)
            ),
            PokemonData::getMetapodData(),
            PokemonData::getButterfreeData(),
            [
                'pokemon_national_dex_number' => 12,
                'pokemon_regional_dex_number' => null,
                'pokemon_order_number' => '9999-0012-003',
                'pokemon_slug' => 'butterfree-f',
                'pokemon_name' => 'Butterfree ♀',
                'pokemon_simplified_name' => 'Butterfree',
                'pokemon_forms_label' => '♀️',
                'pokemon_french_name' => 'Papilusion',
                'pokemon_simplified_french_name' => 'Papilusion',
                'pokemon_forms_french_label' => '♀️',
                'pokemon_icon' => 'butterfree',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => 'gender',
                'variant_form_name' => 'Gender',
                'catch_state_slug' => null,
                'catch_state_name' => null,
                'catch_state_french_name' => null,
                'family_lead_slug' => 'caterpie',
                'pokemon_family_order' => 3,
                'primary_type_slug' => 'bug',
                'primary_type_name' => 'Bug',
                'primary_type_french_name' => 'Insecte',
                'secondary_type_slug' => 'flying',
                'secondary_type_name' => 'Flying',
                'secondary_type_french_name' => 'Vol',
                'original_game_bundle_slug' => 'diamondpearlplatinium',
                'game_bundles' => [],
                'game_bundles_shiny' => [],
            ],
            [
                'pokemon_national_dex_number' => 12,
                'pokemon_regional_dex_number' => null,
                'pokemon_order_number' => '9999-0012-004',
                'pokemon_slug' => 'butterfree-gmax',
                'pokemon_name' => 'Gigantamax Butterfree',
                'pokemon_simplified_name' => 'Butterfree',
                'pokemon_forms_label' => 'Gigantamax',
                'pokemon_french_name' => 'Gigamax Papilusion',
                'pokemon_simplified_french_name' => 'Papilusion',
                'pokemon_forms_french_label' => 'Gigamax',
                'pokemon_icon' => 'butterfree-gmax',
                'category_form_slug' => null,
                'category_form_name' => null,
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => 'gigantamax',
                'special_form_name' => 'Gigantamax',
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => null,
                'catch_state_name' => null,
                'catch_state_french_name' => null,
                'family_lead_slug' => 'caterpie',
                'pokemon_family_order' => 4,
                'primary_type_slug' => 'bug',
                'primary_type_name' => 'Bug',
                'primary_type_french_name' => 'Insecte',
                'secondary_type_slug' => 'flying',
                'secondary_type_name' => 'Flying',
                'secondary_type_french_name' => 'Vol',
                'original_game_bundle_slug' => 'swordshield',
                'game_bundles' => [],
                'game_bundles_shiny' => [],
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    public static function getExpectedRegGreenBlueYellowNestedContent(
        ?string $bulbasaurCatchState,
        ?string $ivysaurCatchState,
        ?string $venusaurCatchState,
        ?string $caterpieCatchState,
        ?string $metapodCatchState,
        ?string $butterfreeCatchState,
        ?string $douzeCatchState,
    ): array {
        /** @var array<array<string, mixed>> $content */
        $content = self::getExpectedRegGreenBlueYellowContent(
            $bulbasaurCatchState,
            $ivysaurCatchState,
            $venusaurCatchState,
            $caterpieCatchState,
            $metapodCatchState,
            $butterfreeCatchState,
            $douzeCatchState,
        );

        return array_map(static fn (array $flat) => self::toNestedFormat($flat), $content);
    }

    /**
     * @return array<array<string, mixed>>
     */
    public static function getExpectedGoldSilverCrystalNestedContent(
        ?string $bulbasaurCatchState,
        ?string $ivysaurCatchState,
        ?string $venusaurCatchState,
        ?string $charmanderCatchState,
        ?string $charmeleonCatchState,
        ?string $charizardCatchState,
        ?string $caterpieCatchState,
        ?string $metapodCatchState,
        ?string $butterfreeCatchState,
    ): array {
        /** @var array<array<string, mixed>> $content */
        $content = self::getExpectedGoldSilverCrystalContent(
            $bulbasaurCatchState,
            $ivysaurCatchState,
            $venusaurCatchState,
            $charmanderCatchState,
            $charmeleonCatchState,
            $charizardCatchState,
            $caterpieCatchState,
            $metapodCatchState,
            $butterfreeCatchState,
        );

        return array_map(static fn (array $flat) => self::toNestedFormat($flat), $content);
    }

    /**
     * @return array<array<string, mixed>>
     */
    public static function getExpectedHomeNestedContent(): array
    {
        /** @var array<array<string, mixed>> $content */
        $content = self::getExpectedHomeContent();

        return array_map(static fn (array $flat) => self::toNestedFormat($flat), $content);
    }

    /**
     * @return array<array<string, mixed>>
     */
    public static function getExpectedHomeShinyNestedContent(): array
    {
        /** @var array<array<string, mixed>> $content */
        $content = self::getExpectedHomeShinyContent();

        return array_map(static fn (array $flat) => self::toNestedFormat($flat), $content);
    }

    /**
     * @param array<string, mixed> $flat
     *
     * @return array<string, mixed>
     */
    private static function toNestedFormat(array $flat): array
    {
        return [
            'pokemon' => [
                'slug' => $flat['pokemon_slug'],
                'name' => $flat['pokemon_name'],
                'french_name' => $flat['pokemon_french_name'],
                'national_dex_number' => $flat['pokemon_national_dex_number'],
                'regional_dex_number' => $flat['pokemon_regional_dex_number'] ?? null,
                'simplified_name' => $flat['pokemon_simplified_name'] ?? null,
                'forms_label' => $flat['pokemon_forms_label'] ?? null,
                'simplified_french_name' => $flat['pokemon_simplified_french_name'] ?? null,
                'forms_french_label' => $flat['pokemon_forms_french_label'] ?? null,
                'icon' => $flat['pokemon_icon'] ?? null,
                'family_order' => $flat['pokemon_family_order'],
                'family_lead_slug' => $flat['family_lead_slug'] ?? null,
                'original_game_bundle_slug' => $flat['original_game_bundle_slug'] ?? null,
                'order_number' => $flat['pokemon_order_number'],
                'game_bundles' => $flat['game_bundles'],
                'game_bundles_shiny' => $flat['game_bundles_shiny'],
            ],
            'catch_state' => self::buildNestedCatchState($flat),
            'forms' => self::buildNestedForms($flat),
            'types' => self::buildNestedTypes($flat),
        ];
    }

    /**
     * @param array<string, mixed> $flat
     *
     * @return null|array<string, mixed>
     */
    private static function buildNestedCatchState(array $flat): ?array
    {
        if (null === ($flat['catch_state_slug'] ?? null)) {
            return null;
        }

        return [
            'slug' => $flat['catch_state_slug'],
            'name' => $flat['catch_state_name'],
            'french_name' => $flat['catch_state_french_name'],
        ];
    }

    /**
     * @param array<string, mixed> $flat
     *
     * @return null|array<string, null|array<string, mixed>>
     */
    private static function buildNestedForms(array $flat): ?array
    {
        $hasAnyForm = null !== ($flat['category_form_slug'] ?? null)
            || null !== ($flat['regional_form_slug'] ?? null)
            || null !== ($flat['special_form_slug'] ?? null)
            || null !== ($flat['variant_form_slug'] ?? null);

        if (!$hasAnyForm) {
            return null;
        }

        return [
            'category' => self::buildNestedForm('category_form', $flat),
            'regional' => self::buildNestedForm('regional_form', $flat),
            'special' => self::buildNestedForm('special_form', $flat),
            'variant' => self::buildNestedForm('variant_form', $flat),
        ];
    }

    /**
     * @param array<string, mixed> $flat
     *
     * @return null|array<string, mixed>
     */
    private static function buildNestedForm(string $prefix, array $flat): ?array
    {
        if (null === ($flat["{$prefix}_slug"] ?? null)) {
            return null;
        }

        return [
            'slug' => $flat["{$prefix}_slug"],
            'name' => $flat["{$prefix}_name"],
        ];
    }

    /**
     * @param array<string, mixed> $flat
     *
     * @return array<string, null|array<string, mixed>>
     */
    private static function buildNestedTypes(array $flat): array
    {
        return [
            'primary' => null !== ($flat['primary_type_slug'] ?? null)
                ? ['slug' => $flat['primary_type_slug'], 'name' => $flat['primary_type_name'], 'french_name' => $flat['primary_type_french_name']]
                : null,
            'secondary' => null !== ($flat['secondary_type_slug'] ?? null)
                ? ['slug' => $flat['secondary_type_slug'], 'name' => $flat['secondary_type_name'], 'french_name' => $flat['secondary_type_french_name']]
                : null,
        ];
    }

    /**
     * @return null[]|string[]
     */
    private static function getCatchStateDataFromSlug(?string $catchStateSlug): array
    {
        switch ($catchStateSlug) {
            case 'yes':
                return [
                    'catch_state_slug' => 'yes',
                    'catch_state_name' => 'Yes',
                    'catch_state_french_name' => 'Oui',
                ];

            case 'maybe':
                return [
                    'catch_state_slug' => 'maybe',
                    'catch_state_name' => 'Maybe',
                    'catch_state_french_name' => 'Peut être',
                ];

            case 'maybenot':
                return [
                    'catch_state_slug' => 'maybenot',
                    'catch_state_name' => 'Maybe not',
                    'catch_state_french_name' => 'Peut être pas',
                ];

            case 'no':
                return [
                    'catch_state_slug' => 'no',
                    'catch_state_name' => 'No',
                    'catch_state_french_name' => 'Non',
                ];

            case null:
            default:
                return [
                    'catch_state_slug' => null,
                    'catch_state_name' => null,
                    'catch_state_french_name' => null,
                ];
        }
    }
}

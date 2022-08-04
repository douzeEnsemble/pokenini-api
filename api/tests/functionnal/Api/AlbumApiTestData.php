<?php

namespace App\Tests\Functionnal\Api;

final class AlbumApiTestData
{
    /**
     * @return string[][]|null[][]
     */
    public static function getExpectedRegGreenBlueYellowContent(): array
    {
        return [
            [
                'pokemon_slug' => 'bulbasaur',
                'pokemon_name' => 'Bulbasaur',
                'pokemon_french_name' => 'Bulbizarre',
                'pokemon_icon' => 'bulbasaur',
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => 'no',
                'catch_state_name' => 'No',
            ],
            [
                'pokemon_slug' => 'ivysaur',
                'pokemon_name' => 'Ivysaur',
                'pokemon_french_name' => 'Herbizarre',
                'pokemon_icon' => 'ivysaur',
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => 'maybe',
                'catch_state_name' => 'Maybe',
            ],
            [
                'pokemon_slug' => 'venusaur',
                'pokemon_name' => 'Venusaur',
                'pokemon_french_name' => 'Florizarre',
                'pokemon_icon' => 'venusaur',
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => 'maybenot',
                'catch_state_name' => 'Maybe not',
            ],
            [
                'pokemon_slug' => 'douze',
                'pokemon_name' => 'Douze',
                'pokemon_french_name' => 'Douze',
                'pokemon_icon' => 'douze',
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => null,
                'catch_state_name' => null,
            ],
        ];
    }

    /**
     * @return string[][]|null[][]
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getExpectedHomeContent(): array
    {
        return [
            [
                'pokemon_slug' => 'bulbasaur',
                'pokemon_name' => 'Bulbasaur',
                'pokemon_french_name' => 'Bulbizarre',
                'pokemon_icon' => 'bulbasaur',
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => 'no',
                'catch_state_name' => 'No',
            ],
            [
                'pokemon_slug' => 'ivysaur',
                'pokemon_name' => 'Ivysaur',
                'pokemon_french_name' => 'Herbizarre',
                'pokemon_icon' => 'ivysaur',
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => 'no',
                'catch_state_name' => 'No',
            ],
            [
                'pokemon_slug' => 'venusaur',
                'pokemon_name' => 'Venusaur',
                'pokemon_french_name' => 'Florizarre',
                'pokemon_icon' => 'venusaur',
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => 'no',
                'catch_state_name' => 'No',
            ],
            [
                'pokemon_slug' => 'venusaurf',
                'pokemon_name' => 'Venusaur ♀',
                'pokemon_french_name' => 'Florizarre ♀',
                'pokemon_icon' => 'venusaur',
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => 'gender',
                'variant_form_name' => 'Gender',
                'catch_state_slug' => 'no',
                'catch_state_name' => 'No',
            ],
            [
                'pokemon_slug' => 'venusaurmega',
                'pokemon_name' => 'Mega Venusaur',
                'pokemon_french_name' => 'Mega Florizarre',
                'pokemon_icon' => 'venusaur',
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => 'mega',
                'special_form_name' => 'Mega',
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => 'no',
                'catch_state_name' => 'No',
            ],
            [
                'pokemon_slug' => 'venusaurgmax',
                'pokemon_name' => 'Gigantamax Venusaur',
                'pokemon_french_name' => 'Gigamax Florizarre',
                'pokemon_icon' => 'venusaur',
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => 'gigantamax',
                'special_form_name' => 'Gigantamax',
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => 'no',
                'catch_state_name' => 'No',
            ],
            [
                'pokemon_slug' => 'douze',
                'pokemon_name' => 'Douze',
                'pokemon_french_name' => 'Douze',
                'pokemon_icon' => 'douze',
                'regional_form_slug' => null,
                'regional_form_name' => null,
                'special_form_slug' => null,
                'special_form_name' => null,
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'catch_state_slug' => null,
                'catch_state_name' => null,
            ],
        ];
    }
}

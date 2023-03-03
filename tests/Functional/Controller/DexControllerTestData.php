<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

final class DexControllerTestData
{
    /**
     * @return string[][]|bool[][]
     */
    public static function getUser12Content(): array
    {
        return [
            [
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'slug' => 'redgreenblueyellow',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home Shiny',
                'french_name' => 'Home Chromatique',
                'slug' => 'homeshiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home PoGo',
                'french_name' => 'Home PoGo',
                'slug' => 'homepogo',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => false,
                'display_template' => 'list-7',
                'is_on_home' => true,
                'is_released' => true,
            ],
        ];
    }

    /**
     * @return string[][]|bool[][]
     */
    public static function getUser12ContentWithUnreleased(): array
    {
        return [
            [
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'slug' => 'redgreenblueyellow',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Gold / Silver / Crystal',
                'french_name' => 'Or / Argent / Cristal',
                'slug' => 'goldsilvercrystal',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => false,
            ],
            [
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home Shiny',
                'french_name' => 'Home Chromatique',
                'slug' => 'homeshiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home PoGo',
                'french_name' => 'Home PoGo',
                'slug' => 'homepogo',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => false,
                'display_template' => 'list-7',
                'is_on_home' => true,
                'is_released' => true,
            ],
        ];
    }

    /**
     * @return string[][]|bool[][]
     */
    public static function getUser13Content(): array
    {
        return [
            [
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'slug' => 'redgreenblueyellow',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home Shiny',
                'french_name' => 'Home Chromatique',
                'slug' => 'homeshiny',
                'is_shiny' => true,
                'is_private' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home PoGo',
                'french_name' => 'Home PoGo',
                'slug' => 'homepogo',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => false,
                'display_template' => 'list-7',
                'is_on_home' => false,
                'is_released' => true,
            ],
        ];
    }

    /**
     * @return string[][]|bool[][]
     */
    public static function getUserUnknownContent(): array
    {
        return [
            [
                'name' => 'Red / Green / Blue / Yellow',
                'french_name' => 'Rouge / Vert / Bleu / Jaune',
                'slug' => 'redgreenblueyellow',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Ruby / Sapphire / Emerald',
                'french_name' => 'Rubis / Saphir / Émeraude',
                'slug' => 'rubysapphireemerald',
                'is_shiny' => false,
                'is_private' => true,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home Shiny',
                'french_name' => 'Home Chromatique',
                'slug' => 'homeshiny',
                'is_shiny' => true,
                'is_private' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_on_home' => false,
                'is_released' => true,
            ],
            [
                'name' => 'Home PoGo',
                'french_name' => 'Home PoGo',
                'slug' => 'homepogo',
                'is_shiny' => false,
                'is_private' => false,
                'is_display_form' => false,
                'display_template' => 'list-7',
                'is_on_home' => false,
                'is_released' => true,
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

/**
 * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
 */
final class DexControllerTestData
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getUser12Content(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald',
                    'french_name' => 'Rubis / Saphir / Émeraude',
                    'slug' => 'rubysapphireemerald',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'main', 'name' => 'Main games', 'french_name' => 'Jeux principaux', 'order_number' => 1],
            ],
            1 => [
                'dex' => ['slug' => 'home'],
                'settings' => [
                    'name' => 'Home',
                    'french_name' => 'Home',
                    'slug' => 'home',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            2 => [
                'dex' => ['slug' => 'homeshiny'],
                'settings' => [
                    'name' => "Home\nShiny",
                    'french_name' => "Home\nChromatique",
                    'slug' => 'home_shiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            3 => [
                'dex' => ['slug' => 'homeshiny'],
                'settings' => [
                    'name' => 'Home Shiny OT',
                    'french_name' => 'Home Chromatique OT',
                    'slug' => 'homeshinyot',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            4 => [
                'dex' => ['slug' => 'demo'],
                'settings' => [
                    'name' => 'Demo',
                    'french_name' => 'Démo',
                    'slug' => 'demo',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
            5 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald: Shiny',
                    'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                    'slug' => 'rubysapphireemeraldshiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getUser12ContentWithUnreleased(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'goldsilvercrystal'],
                'settings' => [
                    'name' => 'Gold / Silver / Crystal',
                    'french_name' => 'Or / Argent / Cristal',
                    'slug' => 'goldsilvercrystal',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => false,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'main', 'name' => 'Main games', 'french_name' => 'Jeux principaux', 'order_number' => 1],
            ],
            1 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald',
                    'french_name' => 'Rubis / Saphir / Émeraude',
                    'slug' => 'rubysapphireemerald',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'main', 'name' => 'Main games', 'french_name' => 'Jeux principaux', 'order_number' => 1],
            ],
            2 => [
                'dex' => ['slug' => 'home'],
                'settings' => [
                    'name' => 'Home',
                    'french_name' => 'Home',
                    'slug' => 'home',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            3 => [
                'dex' => ['slug' => 'homeshiny'],
                'settings' => [
                    'name' => "Home\nShiny",
                    'french_name' => "Home\nChromatique",
                    'slug' => 'home_shiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            4 => [
                'dex' => ['slug' => 'homeshiny'],
                'settings' => [
                    'name' => 'Home Shiny OT',
                    'french_name' => 'Home Chromatique OT',
                    'slug' => 'homeshinyot',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            5 => [
                'dex' => ['slug' => 'homepogo'],
                'settings' => [
                    'name' => 'Home PoGo',
                    'french_name' => 'Home PoGo',
                    'slug' => 'home_pogo',
                    'display_template' => 'list-7',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            6 => [
                'dex' => ['slug' => 'homepogo'],
                'settings' => [
                    'name' => 'Home PoGo OT',
                    'french_name' => 'Home PoGo OT',
                    'slug' => 'homepogoot',
                    'display_template' => 'list-7',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            7 => [
                'dex' => ['slug' => 'homepogo'],
                'settings' => [
                    'name' => 'Home PoGo Poké Ball',
                    'french_name' => 'Home PoGo Poké Ball',
                    'slug' => 'homepogopokeball',
                    'display_template' => 'list-7',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            8 => [
                'dex' => ['slug' => 'demo'],
                'settings' => [
                    'name' => 'Demo',
                    'french_name' => 'Démo',
                    'slug' => 'demo',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
            9 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald: Shiny',
                    'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                    'slug' => 'rubysapphireemeraldshiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getUser12ContentWithPremium(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'redgreenblueyellow'],
                'settings' => [
                    'name' => 'Red / Green / Blue / Yellow',
                    'french_name' => 'Rouge / Vert / Bleu / Jaune',
                    'slug' => 'redgreenblueyellow',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'main', 'name' => 'Main games', 'french_name' => 'Jeux principaux', 'order_number' => 1],
            ],
            1 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald',
                    'french_name' => 'Rubis / Saphir / Émeraude',
                    'slug' => 'rubysapphireemerald',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'main', 'name' => 'Main games', 'french_name' => 'Jeux principaux', 'order_number' => 1],
            ],
            2 => [
                'dex' => ['slug' => 'home'],
                'settings' => [
                    'name' => 'Home',
                    'french_name' => 'Home',
                    'slug' => 'home',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            3 => [
                'dex' => ['slug' => 'homeshiny'],
                'settings' => [
                    'name' => "Home\nShiny",
                    'french_name' => "Home\nChromatique",
                    'slug' => 'home_shiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            4 => [
                'dex' => ['slug' => 'homeshiny'],
                'settings' => [
                    'name' => 'Home Shiny OT',
                    'french_name' => 'Home Chromatique OT',
                    'slug' => 'homeshinyot',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            5 => [
                'dex' => ['slug' => 'demo'],
                'settings' => [
                    'name' => 'Demo',
                    'french_name' => 'Démo',
                    'slug' => 'demo',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
            6 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald: Shiny',
                    'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                    'slug' => 'rubysapphireemeraldshiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getUser12ContentWithUnreleasedAndPremium(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'redgreenblueyellow'],
                'settings' => [
                    'name' => 'Red / Green / Blue / Yellow',
                    'french_name' => 'Rouge / Vert / Bleu / Jaune',
                    'slug' => 'redgreenblueyellow',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'main', 'name' => 'Main games', 'french_name' => 'Jeux principaux', 'order_number' => 1],
            ],
            1 => [
                'dex' => ['slug' => 'goldsilvercrystal'],
                'settings' => [
                    'name' => 'Gold / Silver / Crystal',
                    'french_name' => 'Or / Argent / Cristal',
                    'slug' => 'goldsilvercrystal',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => false,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'main', 'name' => 'Main games', 'french_name' => 'Jeux principaux', 'order_number' => 1],
            ],
            2 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald',
                    'french_name' => 'Rubis / Saphir / Émeraude',
                    'slug' => 'rubysapphireemerald',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'main', 'name' => 'Main games', 'french_name' => 'Jeux principaux', 'order_number' => 1],
            ],
            3 => [
                'dex' => ['slug' => 'home'],
                'settings' => [
                    'name' => 'Home',
                    'french_name' => 'Home',
                    'slug' => 'home',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            4 => [
                'dex' => ['slug' => 'homeshiny'],
                'settings' => [
                    'name' => "Home\nShiny",
                    'french_name' => "Home\nChromatique",
                    'slug' => 'home_shiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            5 => [
                'dex' => ['slug' => 'homeshiny'],
                'settings' => [
                    'name' => 'Home Shiny OT',
                    'french_name' => 'Home Chromatique OT',
                    'slug' => 'homeshinyot',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            6 => [
                'dex' => ['slug' => 'homepogo'],
                'settings' => [
                    'name' => 'Home PoGo',
                    'french_name' => 'Home PoGo',
                    'slug' => 'home_pogo',
                    'display_template' => 'list-7',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            7 => [
                'dex' => ['slug' => 'homepogo'],
                'settings' => [
                    'name' => 'Home PoGo OT',
                    'french_name' => 'Home PoGo OT',
                    'slug' => 'homepogoot',
                    'display_template' => 'list-7',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            8 => [
                'dex' => ['slug' => 'homepogo'],
                'settings' => [
                    'name' => 'Home PoGo Poké Ball',
                    'french_name' => 'Home PoGo Poké Ball',
                    'slug' => 'homepogopokeball',
                    'display_template' => 'list-7',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => false,
                    'is_on_home' => true,
                    'is_display_form' => false,
                    'is_released' => false,
                    'is_premium' => true,
                    'is_custom' => true,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            9 => [
                'dex' => ['slug' => 'demo'],
                'settings' => [
                    'name' => 'Demo',
                    'french_name' => 'Démo',
                    'slug' => 'demo',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
            10 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald: Shiny',
                    'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                    'slug' => 'rubysapphireemeraldshiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getUser13Content(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald',
                    'french_name' => 'Rubis / Saphir / Émeraude',
                    'slug' => 'rubysapphireemerald',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'main', 'name' => 'Main games', 'french_name' => 'Jeux principaux', 'order_number' => 1],
            ],
            1 => [
                'dex' => ['slug' => 'home'],
                'settings' => [
                    'name' => 'Home',
                    'french_name' => 'Home',
                    'slug' => 'home',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            2 => [
                'dex' => ['slug' => 'homeshiny'],
                'settings' => [
                    'name' => "Home\nShiny",
                    'french_name' => "Home\nChromatique",
                    'slug' => 'homeshiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            3 => [
                'dex' => ['slug' => 'demo'],
                'settings' => [
                    'name' => 'Demo',
                    'french_name' => 'Démo',
                    'slug' => 'demo',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
            4 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald: Shiny',
                    'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                    'slug' => 'rubysapphireemeraldshiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getUserUnknownContent(): array
    {
        return [
            0 => [
                'dex' => ['slug' => 'rubysapphireemerald'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald',
                    'french_name' => 'Rubis / Saphir / Émeraude',
                    'slug' => 'rubysapphireemerald',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'main', 'name' => 'Main games', 'french_name' => 'Jeux principaux', 'order_number' => 1],
            ],
            1 => [
                'dex' => ['slug' => 'home'],
                'settings' => [
                    'name' => 'Home',
                    'french_name' => 'Home',
                    'slug' => 'home',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            2 => [
                'dex' => ['slug' => 'homeshiny'],
                'settings' => [
                    'name' => "Home\nShiny",
                    'french_name' => "Home\nChromatique",
                    'slug' => 'homeshiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => ['slug' => 'home', 'name' => 'Home', 'french_name' => 'Home', 'order_number' => 2],
            ],
            3 => [
                'dex' => ['slug' => 'demo'],
                'settings' => [
                    'name' => 'Demo',
                    'french_name' => 'Démo',
                    'slug' => 'demo',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => false,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
            4 => [
                'dex' => ['slug' => 'rubysapphireemeraldshiny'],
                'settings' => [
                    'name' => 'Ruby / Sapphire / Emerald: Shiny',
                    'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                    'slug' => 'rubysapphireemeraldshiny',
                    'display_template' => 'box',
                ],
                'flags' => [
                    'is_shiny' => true,
                    'is_private' => true,
                    'is_on_home' => false,
                    'is_display_form' => true,
                    'is_released' => true,
                    'is_premium' => false,
                    'is_custom' => false,
                ],
                'category' => null,
            ],
        ];
    }
}

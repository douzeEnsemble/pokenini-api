<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

class DexesApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[] $content */
        $content = $this->apiRequestContent('dexes');

        $this->assertEquals([
            'name' => 'Red / Green / Blue / Yellow',
            'frenchName' => 'Rouge / Vert / Bleu / Jaune',
            'slug' => 'redgreenblueyellow',
            'isShiny' => false,
            'isPrivate' => true,
            'isDisplayForm' => true,
            'displayTemplate' => 'box',
            'region' => [
                'name' => 'Kanto',
                'frenchName' => 'Kanto',
                'slug' => 'kanto',
            ],
            'description' => 'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            'frenchDescription' => 'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            'version' => 1,
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Gold / Silver / Crystal',
            'frenchName' => 'Or / Argent / Cristal',
            'slug' => 'goldsilvercrystal',
            'isShiny' => false,
            'isPrivate' => true,
            'isDisplayForm' => true,
            'displayTemplate' => 'box',
            'region' => [
                'name' => 'Johto',
                'frenchName' => 'Johto',
                'slug' => 'johto',
            ],
            'description' => 'The list of obtainable Pokémons in Gold, Silver and Crystal games',
            'frenchDescription' => 'La liste des pokémons obtenable dans les jeux Or, Argent et Cristal.',
            'version' => 1,
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Home',
            'frenchName' => 'Home',
            'slug' => 'home',
            'isShiny' => false,
            'isPrivate' => false,
            'isDisplayForm' => true,
            'displayTemplate' => 'box',
            'description' => '',
            'frenchDescription' => '',
            'version' => 2,
        ], $content[3]);

        $this->assertEquals([
            'name' => 'Home Shiny',
            'frenchName' => 'Home Chromatique',
            'slug' => 'homeshiny',
            'isShiny' => true,
            'isPrivate' => false,
            'isDisplayForm' => true,
            'displayTemplate' => 'box',
            'description' => '',
            'frenchDescription' => '',
            'version' => 1,
        ], $content[4]);

        $this->assertEquals([
            'name' => 'Home PoGo',
            'frenchName' => 'Home PoGo',
            'slug' => 'homepogo',
            'isShiny' => false,
            'isPrivate' => false,
            'isDisplayForm' => false,
            'displayTemplate' => 'list-7',
            'description' => '',
            'frenchDescription' => '',
            'version' => 1,
        ], $content[5]);
    }
}

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
            'regionName' => 'Kanto',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Gold / Silver / Crystal',
            'frenchName' => 'Or / Argent / Cristal',
            'slug' => 'goldsilvercrystal',
            'isShiny' => false,
            'isPrivate' => true,
            'isDisplayForm' => true,
            'displayTemplate' => 'box',
            'regionName' => 'Johto',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Home',
            'frenchName' => 'Home',
            'slug' => 'home',
            'isShiny' => false,
            'isPrivate' => false,
            'isDisplayForm' => true,
            'displayTemplate' => 'box',
        ], $content[3]);

        $this->assertEquals([
            'name' => 'Home Shiny',
            'frenchName' => 'Home Chromatique',
            'slug' => 'homeshiny',
            'isShiny' => true,
            'isPrivate' => false,
            'isDisplayForm' => true,
            'displayTemplate' => 'box',
        ], $content[4]);

        $this->assertEquals([
            'name' => 'Home PoGo',
            'frenchName' => 'Home PoGo',
            'slug' => 'homepogo',
            'isShiny' => false,
            'isPrivate' => false,
            'isDisplayForm' => false,
            'displayTemplate' => 'list-7',
        ], $content[5]);
    }
}

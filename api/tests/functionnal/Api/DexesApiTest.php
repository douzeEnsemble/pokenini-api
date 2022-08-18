<?php

namespace App\Tests\Functionnal\Api;

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
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Gold / Silver / Crystal',
            'frenchName' => 'Or / Argent / Cristal',
            'slug' => 'goldsilvercrystal',
            'isShiny' => false,
            'isPrivate' => true,
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Home',
            'frenchName' => 'Home',
            'slug' => 'home',
            'isShiny' => false,
            'isPrivate' => false,
        ], $content[3]);

        $this->assertEquals([
            'name' => 'Home Shiny',
            'frenchName' => 'Home Chromatique',
            'slug' => 'homeshiny',
            'isShiny' => true,
            'isPrivate' => false,
        ], $content[4]);
    }
}

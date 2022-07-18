<?php

namespace App\Tests\functionnal\Api;

class PokemonsApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[]|int[]|bool[]|string[][] $content */
        $content = $this->apiRequestContent('pokemon');

        $this->assertEquals([
            'nationalDexNumber' => 1,
            'bankable' => true,
            'originalGameBundle' => [
                'generation' => [
                    'name' => '1',
                    'slug' => '1',
                ],
                'name' => 'Red, Green, Blue, Yellow',
                'slug' => 'redgreenblueyellow',
            ],
            'iconName' => 'bulbasaur',
            'familyOrder' => 0,
            'name' => 'Bulbasaur',
            'slug' => 'bulbasaur',
        ], $content[0]);

        $this->assertEquals([
            'nationalDexNumber' => 2,
            'family' => [
                'nationalDexNumber' => 1,
                'bankable' => true,
                'originalGameBundle' => [
                    'generation' => [
                        'name' => '1',
                        'slug' => '1',
                    ],
                    'name' => 'Red, Green, Blue, Yellow',
                    'slug' => 'redgreenblueyellow',
                ],
                'iconName' => 'bulbasaur',
                'familyOrder' => 0,
                'name' => 'Bulbasaur',
                'slug' => 'bulbasaur',
            ],
            'bankable' => true,
            'originalGameBundle' => [
                'generation' =>
                    [
                        'name' => '1',
                        'slug' => '1',
                    ],
                'name' => 'Red, Green, Blue, Yellow',
                'slug' => 'redgreenblueyellow',
            ],
            'iconName' => 'ivysaur',
            'familyOrder' => 1,
            'name' => 'Ivysaur',
            'slug' => 'ivysaur',
        ], $content[1]);

        $this->assertEquals([
            'nationalDexNumber' => 3,
            'family' => [
                'nationalDexNumber' => 1,
                'bankable' => true,
                'originalGameBundle' => [
                    'generation' => [
                        'name' => '1',
                        'slug' => '1',
                    ],
                    'name' => 'Red, Green, Blue, Yellow',
                    'slug' => 'redgreenblueyellow',
                ],
                'iconName' => 'bulbasaur',
                'familyOrder' => 0,
                'name' => 'Bulbasaur',
                'slug' => 'bulbasaur',
            ],
            'bankable' => true,
            'originalGameBundle' => [
                'generation' => [
                    'name' => '6',
                    'slug' => '6',
                ],
                'name' => 'X, Y',
                'slug' => 'xy',
            ],
            'specialForm' => [
                'name' => 'Mega',
                'slug' => 'mega',
            ],
            'iconName' => 'venusaur',
            'familyOrder' => 4,
            'name' => 'Mega Venusaur',
            'slug' => 'megavenusaur',
        ], $content[4]);
    }
}

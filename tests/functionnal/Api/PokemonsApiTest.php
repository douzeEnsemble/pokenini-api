<?php

declare(strict_types=1);

namespace App\Tests\Functionnal\Api;

class PokemonsApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[][]|int[][]|bool[][]|string[][][]|int[][][]|bool[][][] $content */
        $content = $this->apiRequestContent('pokemon');

        $this->assertBulbasaur($content[0]);

        $this->assertIvysaur($content[1]);

        $this->assertVenusaur($content[4]);
    }

    /**
     * @param string[]|int[]|bool[]|string[][]|int[][]|bool[][] $data
     */
    private function assertBulbasaur(array $data): void
    {
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
            'primeName' => 'Bulbasaur',
            'frenchName' => 'Bulbizarre',
            'simplifiedName' => 'Bulbasaur',
            'simplifiedFrenchName' => 'Bulbizarre',
            'formsLabel' => '',
            'formsFrenchLabel' => '',
            'categoryForm' => [
                'name' => 'Starter',
                'slug' => 'starter',
            ],
        ], $data);
    }

    /**
     * @param string[]|int[]|bool[]|string[][]|int[][]|bool[][] $data
     */
    private function assertIvysaur(array $data): void
    {
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
                'primeName' => 'Bulbasaur',
                'frenchName' => 'Bulbizarre',
                'simplifiedName' => 'Bulbasaur',
                'simplifiedFrenchName' => 'Bulbizarre',
                'formsLabel' => '',
                'formsFrenchLabel' => '',
                'categoryForm' => [
                    'name' => 'Starter',
                    'slug' => 'starter',
                ],
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
            'primeName' => 'Ivysaur',
            'frenchName' => 'Herbizarre',
            'simplifiedName' => 'Ivysaur',
            'simplifiedFrenchName' => 'Herbizarre',
            'formsLabel' => '',
            'formsFrenchLabel' => '',
        ], $data);
    }

    /**
     * @param string[]|int[]|bool[]|string[][]|int[][]|bool[][] $data
     */
    private function assertVenusaur(array $data): void
    {
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
                'primeName' => 'Bulbasaur',
                'frenchName' => 'Bulbizarre',
                'simplifiedName' => 'Bulbasaur',
                'simplifiedFrenchName' => 'Bulbizarre',
                'formsLabel' => '',
                'formsFrenchLabel' => '',
                'categoryForm' => [
                    'name' => 'Starter',
                    'slug' => 'starter',
                ],
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
            'slug' => 'venusaur-mega',
            'primeName' => 'Venusaur',
            'frenchName' => 'Mega Florizarre',
            'simplifiedName' => 'Venusaur',
            'simplifiedFrenchName' => 'Florizarre',
            'formsLabel' => 'Mega',
            'formsFrenchLabel' => 'Mega',
        ], $data);
    }
}

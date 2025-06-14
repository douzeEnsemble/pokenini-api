<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ElectionPokemonsList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionPokemonsList::class)]
class ElectionPokemonsListTest extends TestCase
{
    public function testOk(): void
    {
        $attributes = new ElectionPokemonsList(
            'pick',
            [
                [
                    'pokemon_slug' => 'pichu',
                    'regional_form_slug' => null,
                    'pokemon_family_order' => 0,
                ],
                [
                    'pokemon_slug' => 'raichu',
                    'regional_form_slug' => null,
                    'pokemon_family_order' => 2,
                ],
            ],
        );

        $this->assertSame('pick', $attributes->getListType());
        $this->assertSame(
            [
                [
                    'pokemon_slug' => 'pichu',
                    'regional_form_slug' => null,
                    'pokemon_family_order' => 0,
                ],
                [
                    'pokemon_slug' => 'raichu',
                    'regional_form_slug' => null,
                    'pokemon_family_order' => 2,
                ],
            ],
            $attributes->getItems()
        );
    }
}

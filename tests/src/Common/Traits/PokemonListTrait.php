<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits;

/**
 * @psalm-import-type PokedexRepositoryItems from \App\Tests\Common\Types\PokedexTypes
 * @psalm-import-type PokedexResponseItems from \App\Tests\Common\Types\PokedexTypes
 */
trait PokemonListTrait
{
    /**
     * @param PokedexRepositoryItems|PokedexResponseItems $list
     * @param array<int, string>                          $slugs
     */
    public function assertResponseSameSlugs(array $list, array $slugs): void
    {
        $items = array_column($list, 'pokemon');
        $data = array_column($items, 'slug');

        $this->assertSame($data, $slugs);
    }

    /**
     * @param PokedexRepositoryItems|PokedexResponseItems $list
     * @param array<int, string>                          $slugs
     */
    public function assertSameSlugs(array $list, array $slugs): void
    {
        $data = array_column($list, 'pokemon_slug');

        $this->assertSame($data, $slugs);
    }
}

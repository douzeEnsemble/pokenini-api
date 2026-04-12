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
    public function assertSameSlugs(array $list, array $slugs): void
    {
        $items = $list;
        array_walk($items, fn (array &$item): mixed => $item = $item['pokemon_slug']);

        /** @var array<int, string> $data */
        $data = $items;

        $this->assertSame($data, $slugs);
    }
}

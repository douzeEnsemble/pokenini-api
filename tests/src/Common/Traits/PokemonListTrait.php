<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits;

trait PokemonListTrait
{
    /**
     * @param int[][]|string[][]|string[][][] $list
     * @param array<int, string>              $slugs
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

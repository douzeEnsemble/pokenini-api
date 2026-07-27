<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PokemonImageCreditRepository;

class ImageCreditsService
{
    public function __construct(
        private readonly PokemonImageCreditRepository $repository,
    ) {}

    /**
     * @return array<array{
     *   source: string,
     *   images: array<array{
     *     pokemon_slug: string,
     *     pokemon_name: string,
     *     pokemon_french_name: string,
     *     pokemon_icon: string,
     *     size: string,
     *     is_shiny: bool,
     *   }>,
     * }>
     */
    public function getAllGroupedBySource(): array
    {
        $rows = $this->repository->findAllWithPokemon();

        /** @var array<string, array{source: string, images: array<array{pokemon_slug: string, pokemon_name: string, pokemon_french_name: string, pokemon_icon: string, size: string, is_shiny: bool}>}> $grouped */
        $grouped = [];
        foreach ($rows as $row) {
            $source = $row['source'];
            unset($row['source']);

            $grouped[$source]['source'] = $source;
            $grouped[$source]['images'][] = $row;
        }

        usort(
            $grouped,
            static function (array $groupA, array $groupB): int {
                /** @var array<array{pokemon_slug: string, pokemon_name: string, pokemon_french_name: string, pokemon_icon: string, size: string, is_shiny: bool}> $imagesA */
                $imagesA = $groupA['images'];

                /** @var array<array{pokemon_slug: string, pokemon_name: string, pokemon_french_name: string, pokemon_icon: string, size: string, is_shiny: bool}> $imagesB */
                $imagesB = $groupB['images'];
                $countComparison = count($imagesB) <=> count($imagesA);

                return 0 !== $countComparison ? $countComparison : $groupA['source'] <=> $groupB['source'];
            },
        );

        return $grouped;
    }
}

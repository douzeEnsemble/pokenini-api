<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\PokemonImageCreditRepository;
use App\Service\ImageCreditsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImageCreditsService::class)]
final class ImageCreditsServiceTest extends TestCase
{
    private MockObject&PokemonImageCreditRepository $repository;
    private ImageCreditsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PokemonImageCreditRepository::class);
        $this->service = new ImageCreditsService($this->repository);
    }

    #[Test]
    public function getAllGroupedBySourceGroupsRowsBySourceAndSortsByImageCountDescending(): void
    {
        $imageA1 = ['pokemon_slug' => 'a1', 'pokemon_name' => 'A1', 'pokemon_french_name' => 'A1fr', 'pokemon_icon' => 'a1', 'size' => 'small', 'is_shiny' => false];
        $imageA2 = ['pokemon_slug' => 'a2', 'pokemon_name' => 'A2', 'pokemon_french_name' => 'A2fr', 'pokemon_icon' => 'a2', 'size' => 'big', 'is_shiny' => false];
        $imageB1 = ['pokemon_slug' => 'b1', 'pokemon_name' => 'B1', 'pokemon_french_name' => 'B1fr', 'pokemon_icon' => 'b1', 'size' => 'small', 'is_shiny' => true];

        $this->repository
            ->expects(self::once())
            ->method('findAllWithPokemon')
            ->willReturn([
                ['source' => 'SourceB', ...$imageB1],
                ['source' => 'SourceA', ...$imageA1],
                ['source' => 'SourceA', ...$imageA2],
            ])
        ;

        $result = $this->service->getAllGroupedBySource();

        self::assertSame(
            [
                ['source' => 'SourceA', 'images' => [$imageA1, $imageA2]],
                ['source' => 'SourceB', 'images' => [$imageB1]],
            ],
            $result,
        );
    }

    #[Test]
    public function getAllGroupedBySourceBreaksImageCountTiesAlphabeticallyBySource(): void
    {
        $image = ['pokemon_slug' => 'x', 'pokemon_name' => 'X', 'pokemon_french_name' => 'Xfr', 'pokemon_icon' => 'x', 'size' => 'small', 'is_shiny' => false];

        $this->repository
            ->expects(self::once())
            ->method('findAllWithPokemon')
            ->willReturn([
                ['source' => 'Zebra', ...$image],
                ['source' => 'Alpha', ...$image],
            ])
        ;

        $result = $this->service->getAllGroupedBySource();

        self::assertSame('Alpha', $result[0]['source']);
        self::assertSame('Zebra', $result[1]['source']);
    }

    #[Test]
    public function getAllGroupedBySourceReturnsEmptyArrayWhenRepositoryHasNoRows(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('findAllWithPokemon')
            ->willReturn([])
        ;

        self::assertSame([], $this->service->getAllGroupedBySource());
    }
}

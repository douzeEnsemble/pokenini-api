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
    public function getAllByPokemonReturnsTheRepositoryRowsUnchanged(): void
    {
        $rows = [
            [
                'pokemon_slug' => 'bulbasaur',
                'pokemon_name' => 'Bulbasaur',
                'pokemon_french_name' => 'Bulbizarre',
                'pokemon_icon' => 'bulbasaur',
                'small_regular_credit' => 'PokéSprite - https://github.com/msikma/pokesprite',
                'small_shiny_credit' => null,
                'big_regular_credit' => null,
                'big_shiny_credit' => null,
            ],
        ];

        $this->repository
            ->expects(self::once())
            ->method('findAllPokemonWithCredits')
            ->willReturn($rows)
        ;

        self::assertSame($rows, $this->service->getAllByPokemon());
    }

    #[Test]
    public function getAllByPokemonReturnsEmptyArrayWhenRepositoryHasNoRows(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('findAllPokemonWithCredits')
            ->willReturn([])
        ;

        self::assertSame([], $this->service->getAllByPokemon());
    }
}

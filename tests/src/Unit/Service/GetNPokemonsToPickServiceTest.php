<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\TrainerPokemonEloListQueryOptions;
use App\Repository\PokemonsRepository;
use App\Service\GetNPokemonsToPickService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GetNPokemonsToPickService::class)]
final class GetNPokemonsToPickServiceTest extends TestCase
{
    public function testgetNPokemonsToPick(): void
    {
        $queryOptions = new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
            'dex_slug' => 'demo',
            'election_slug' => 'pref',
            'count' => 12,
        ]);

        $repository = $this->createMock(PokemonsRepository::class);
        $repository->expects($this->once())
            ->method('getNToPick')
            ->with(
                'demo',
                12,
                'bd307a3ec329e10a2cff8fb87480823da114f8f4',
                'pref',
                $queryOptions->albumFilters,
                1000
            )
            ->willReturn(
                [
                    [
                        'toto',
                    ],
                    [
                        'titi',
                    ],
                ],
            )
        ;

        $service = new GetNPokemonsToPickService(
            $repository,
            1000,
        );

        $this->assertEquals(
            [
                [
                    'toto',
                ],
                [
                    'titi',
                ],
            ],
            $service->getNPokemonsToPick($queryOptions)
        );
    }
}

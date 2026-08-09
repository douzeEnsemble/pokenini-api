<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\TrainerPokemonEloListQueryOptions;
use App\Service\GetNPokemonsToChooseService;
use App\Service\GetNPokemonsToPickService;
use App\Service\GetNPokemonsToVoteService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GetNPokemonsToChooseService::class)]
final class GetNPokemonsToChooseServiceTest extends TestCase
{
    #[Test]
    public function fromToPick(): void
    {
        $queryOptions = new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
            'dex_slug' => 'demo',
            'election_slug' => 'pref',
            'count' => 12,
        ]);

        $toPickService = $this->createMock(GetNPokemonsToPickService::class);
        $toPickService->expects($this->once())
            ->method('getNPokemonsToPick')
            ->with($queryOptions)
            ->willReturn(
                [
                    ['pokemon_slug' => 'toto'],
                    ['pokemon_slug' => 'titi'],
                ],
            )
        ;

        $toVoteService = $this->createMock(GetNPokemonsToVoteService::class);
        $toVoteService->expects($this->never())
            ->method('getNPokemonsToVote')
        ;

        $service = new GetNPokemonsToChooseService(
            $toPickService,
            $toVoteService,
        );

        $electionList = $service->getNPokemonsToChoose($queryOptions);

        $this->assertSame('pick', $electionList->getListType());
        $this->assertSame(
            [
                ['pokemon_slug' => 'toto'],
                ['pokemon_slug' => 'titi'],
            ],
            $electionList->getItems(),
        );
    }

    #[Test]
    public function fromToVote(): void
    {
        $queryOptions = new TrainerPokemonEloListQueryOptions([
            'trainer_external_id' => 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
            'dex_slug' => 'demo',
            'election_slug' => 'pref',
            'count' => 12,
        ]);

        $toPickService = $this->createMock(GetNPokemonsToPickService::class);
        $toPickService->expects($this->once())
            ->method('getNPokemonsToPick')
            ->with($queryOptions)
            ->willReturn([])
        ;

        $toVoteService = $this->createMock(GetNPokemonsToVoteService::class);
        $toVoteService->expects($this->once())
            ->method('getNPokemonsToVote')
            ->with($queryOptions)
            ->willReturn(
                [
                    ['pokemon_slug' => 'tata'],
                    ['pokemon_slug' => 'tutu'],
                ],
            )
        ;

        $service = new GetNPokemonsToChooseService(
            $toPickService,
            $toVoteService,
        );

        $electionList = $service->getNPokemonsToChoose($queryOptions);

        $this->assertSame('vote', $electionList->getListType());
        $this->assertSame(
            [
                ['pokemon_slug' => 'tata'],
                ['pokemon_slug' => 'tutu'],
            ],
            $electionList->getItems(),
        );
    }
}

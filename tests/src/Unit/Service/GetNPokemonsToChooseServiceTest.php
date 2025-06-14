<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\TrainerPokemonEloListQueryOptions;
use App\Service\GetNPokemonsToChooseService;
use App\Service\GetNPokemonsToPickService;
use App\Service\GetNPokemonsToVoteService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GetNPokemonsToChooseService::class)]
class GetNPokemonsToChooseServiceTest extends TestCase
{
    public function testFromToPick(): void
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
                    [
                        'toto',
                    ],
                    [
                        'titi',
                    ],
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
                [
                    'toto',
                ],
                [
                    'titi',
                ],
            ],
            $electionList->getItems(),
        );
    }

    public function testFromToVote(): void
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
                    [
                        'tata',
                    ],
                    [
                        'tutu',
                    ],
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
                [
                    'tata',
                ],
                [
                    'tutu',
                ],
            ],
            $electionList->getItems(),
        );
    }
}

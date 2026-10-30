<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Exception\WhichShinyGameNotFoundException;
use App\Repository\WhichShinyGameRepository;
use App\Service\WhichShinyGameService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

/**
 * @internal
 */
#[CoversClass(WhichShinyGameService::class)]
final class WhichShinyGameServiceRoundsTest extends TestCase
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';
    private const string GAME_ID = '01a15aac-cfd2-7085-bbc7-88594aa005dd';

    private MockObject&WhichShinyGameRepository $repository;
    private WhichShinyGameService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->repository = $this->createMock(WhichShinyGameRepository::class);
        $this->service = new WhichShinyGameService(
            $this->repository,
            new Randomizer(new Xoshiro256StarStar(12)),
        );
    }

    #[Test]
    public function getRounds(): void
    {
        $game = ['id' => self::GAME_ID];
        $rounds = [
            ['position' => 1, 'pokemon_id' => 'p1', 'shiny_slot' => 1, 'chosen_slot' => 2, 'is_correct' => false],
            ['position' => 2, 'pokemon_id' => 'p2', 'shiny_slot' => 2, 'chosen_slot' => 2, 'is_correct' => true],
        ];

        $this->repository
            ->expects($this->once())
            ->method('getGame')
            ->with(self::TRAINER, self::GAME_ID)
            ->willReturn($game)
        ;

        $this->repository
            ->expects($this->once())
            ->method('getAnsweredRounds')
            ->with(self::GAME_ID)
            ->willReturn($rounds)
        ;

        $this->assertSame(
            [
                'game' => $game,
                'rounds' => $rounds,
            ],
            $this->service->getRounds(self::TRAINER, self::GAME_ID),
        );
    }

    #[Test]
    public function getRoundsOfAnUnknownGame(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('getGame')
            ->with(self::TRAINER, self::GAME_ID)
            ->willReturn(null)
        ;

        $this->repository
            ->expects($this->never())
            ->method('getAnsweredRounds')
        ;

        $this->expectException(WhichShinyGameNotFoundException::class);
        $this->expectExceptionMessageIsOrContains('Game '.self::GAME_ID.' not found');

        $this->service->getRounds(self::TRAINER, self::GAME_ID);
    }
}

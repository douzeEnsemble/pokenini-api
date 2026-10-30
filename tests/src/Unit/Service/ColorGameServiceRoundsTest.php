<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Exception\ColorGameNotFoundException;
use App\Repository\ColorGameRepository;
use App\Service\ColorGameService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

/**
 * @internal
 */
#[CoversClass(ColorGameService::class)]
final class ColorGameServiceRoundsTest extends TestCase
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';
    private const string GAME_ID = '01a15aac-cfd2-7085-bbc7-88594aa005dd';

    private MockObject&ColorGameRepository $repository;
    private ColorGameService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->repository = $this->createMock(ColorGameRepository::class);
        $this->service = new ColorGameService(
            $this->repository,
            new Randomizer(new Xoshiro256StarStar(12)),
        );
    }

    #[Test]
    public function getRounds(): void
    {
        $game = ['id' => self::GAME_ID, 'is_shiny' => false];
        $round1 = ['position' => 1, 'pokemon_id' => 'p1', 'choices_order' => '2,1,3', 'chosen_choice' => 1, 'is_correct' => true];
        $round2 = ['position' => 2, 'pokemon_id' => 'p2', 'choices_order' => '1,2', 'chosen_choice' => 1, 'is_correct' => false];
        $round3 = ['position' => 3, 'pokemon_id' => 'p3', 'choices_order' => '2,3,1', 'chosen_choice' => 2, 'is_correct' => true];
        $set11 = ['round_position' => 1, 'position' => 1, 'codes' => '#111111'];
        $set12 = ['round_position' => 1, 'position' => 2, 'codes' => '#121212'];
        $set21 = ['round_position' => 2, 'position' => 1, 'codes' => '#212121'];

        $this->repository
            ->expects($this->once())
            ->method('getGame')
            ->with(self::TRAINER, self::GAME_ID)
            ->willReturn($game)
        ;

        $this->repository
            ->expects($this->once())
            ->method('getAnsweredRoundsColorSets')
            ->with(self::GAME_ID)
            ->willReturn([$set11, $set12, $set21])
        ;

        $this->repository
            ->expects($this->once())
            ->method('getAnsweredRounds')
            ->with(self::GAME_ID)
            ->willReturn([$round1, $round2, $round3])
        ;

        // The colour sets of a Pokémon can have been removed since the game was played
        $this->assertSame(
            [
                'game' => $game,
                'rounds' => [
                    ['round' => $round1, 'color_sets' => [$set11, $set12], 'correct_choice' => 1],
                    ['round' => $round2, 'color_sets' => [$set21], 'correct_choice' => 0],
                    ['round' => $round3, 'color_sets' => [], 'correct_choice' => 2],
                ],
            ],
            $this->service->getRounds(self::TRAINER, self::GAME_ID),
        );
    }

    #[Test]
    public function getRoundsWithoutAnsweredRounds(): void
    {
        $game = ['id' => self::GAME_ID, 'is_shiny' => true];

        $this->repository
            ->method('getGame')
            ->willReturn($game)
        ;

        $this->repository
            ->method('getAnsweredRoundsColorSets')
            ->willReturn([])
        ;

        $this->repository
            ->method('getAnsweredRounds')
            ->willReturn([])
        ;

        $this->assertSame(
            [
                'game' => $game,
                'rounds' => [],
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

        $this->repository
            ->expects($this->never())
            ->method('getAnsweredRoundsColorSets')
        ;

        $this->expectException(ColorGameNotFoundException::class);
        $this->expectExceptionMessageIsOrContains('Game '.self::GAME_ID.' not found');

        $this->service->getRounds(self::TRAINER, self::GAME_ID);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\ColorGameAnswer;
use App\DTO\DexQueryOptions;
use App\Exception\ColorGameConflictException;
use App\Exception\ColorGameNotFoundException;
use App\Exception\InvalidColorGameRequestException;
use App\Repository\ColorGameRepository;
use App\Service\ColorGameService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

/**
 * @internal
 */
#[CoversClass(ColorGameService::class)]
final class ColorGameServiceTest extends TestCase
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';
    private const string GAME_ID = '01a15aac-cfd2-7085-bbc7-88594aa005dd';
    private const int SEED = 12;

    private ColorGameRepository&MockObject $repository;
    private ColorGameService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->repository = $this->createMock(ColorGameRepository::class);
        $this->service = new ColorGameService(
            $this->repository,
            new Randomizer(new Xoshiro256StarStar(self::SEED)),
        );
    }

    #[Test]
    public function getPlayableDexList(): void
    {
        $options = new DexQueryOptions();
        $rows = [['slug' => 'demo']];

        $this->repository
            ->expects($this->once())
            ->method('getPlayableDexList')
            ->with($options)
            ->willReturn($rows)
        ;

        $this->assertSame($rows, $this->service->getPlayableDexList($options));
    }

    #[Test]
    public function getInProgressGamesByDex(): void
    {
        $rows = ['demo' => ['id' => self::GAME_ID]];

        $this->repository
            ->expects($this->once())
            ->method('getInProgressGamesByDex')
            ->with(self::TRAINER)
            ->willReturn($rows)
        ;

        $this->assertSame($rows, $this->service->getInProgressGamesByDex(self::TRAINER));
    }

    #[Test]
    public function getGames(): void
    {
        $rows = [['id' => self::GAME_ID]];

        $this->repository
            ->expects($this->once())
            ->method('getGames')
            ->with(self::TRAINER)
            ->willReturn($rows)
        ;

        $this->assertSame($rows, $this->service->getGames(self::TRAINER));
    }

    #[Test]
    public function create(): void
    {
        $pokemons = [
            ['pokemon_id' => 'p1', 'positions' => '1,2,3,4,5'],
            ['pokemon_id' => 'p2', 'positions' => '1,2'],
            ['pokemon_id' => 'p3', 'positions' => '1,2,3'],
        ];

        $randomizer = new Randomizer(new Xoshiro256StarStar(self::SEED));
        $expectedRounds = array_map(
            static fn (array $pokemon): array => [
                'pokemon_id' => $pokemon['pokemon_id'],
                'choices_order' => $randomizer->shuffleArray(
                    array_map(intval(...), explode(',', $pokemon['positions'])),
                ),
            ],
            $randomizer->shuffleArray($pokemons),
        );

        $this->repository
            ->expects($this->once())
            ->method('getPlayableDex')
            ->with('demo')
            ->willReturn(['id' => 'dex-id', 'is_shiny' => true])
        ;

        $this->repository
            ->expects($this->once())
            ->method('getPlayablePokemons')
            ->with('dex-id', true)
            ->willReturn($pokemons)
        ;

        $gameId = null;
        $this->repository
            ->expects($this->once())
            ->method('startGame')
            ->willReturnCallback(function (string $startedGameId, string $trainer, string $dexId, bool $isShiny, array $rounds) use (&$gameId, $expectedRounds): void {
                $gameId = $startedGameId;

                $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}$/', $startedGameId);
                $this->assertSame(self::TRAINER, $trainer);
                $this->assertSame('dex-id', $dexId);
                $this->assertTrue($isShiny);
                $this->assertSame($expectedRounds, $rounds);
            })
        ;

        $game = ['id' => 'new-game'];
        $this->repository
            ->expects($this->once())
            ->method('getGame')
            ->willReturnCallback(function (string $trainer, string $requestedGameId) use (&$gameId, $game): array {
                $this->assertSame(self::TRAINER, $trainer);
                $this->assertSame($gameId, $requestedGameId);

                return $game;
            })
        ;

        $this->assertSame($game, $this->service->create(self::TRAINER, 'demo'));

        // Every choice keeps all the positions
        $choices = array_column($expectedRounds, 'choices_order', 'pokemon_id');
        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5], $choices['p1']);
        $this->assertEqualsCanonicalizing([1, 2], $choices['p2']);
        $this->assertEqualsCanonicalizing([1, 2, 3], $choices['p3']);
    }

    #[Test]
    #[DataProvider('providerCreateWithRoundCount')]
    public function createWithRoundCount(?int $roundCount, int $expectedCount): void
    {
        $pokemons = [
            ['pokemon_id' => 'p1', 'positions' => '1,2'],
            ['pokemon_id' => 'p2', 'positions' => '1,2'],
            ['pokemon_id' => 'p3', 'positions' => '1,2'],
            ['pokemon_id' => 'p4', 'positions' => '1,2'],
            ['pokemon_id' => 'p5', 'positions' => '1,2'],
        ];
        $shuffledIds = array_column((new Randomizer(new Xoshiro256StarStar(self::SEED)))->shuffleArray($pokemons), 'pokemon_id');

        $this->repository
            ->method('getPlayableDex')
            ->willReturn(['id' => 'dex-id', 'is_shiny' => false])
        ;

        $this->repository
            ->method('getPlayablePokemons')
            ->willReturn($pokemons)
        ;

        // The first Pokémon of the shuffled dex are played
        $this->repository
            ->expects($this->once())
            ->method('startGame')
            ->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), $this->callback(
                static fn (array $rounds): bool => array_slice($shuffledIds, 0, $expectedCount) === array_column($rounds, 'pokemon_id'),
            ))
        ;

        $this->repository
            ->method('getGame')
            ->willReturn(['id' => 'new-game'])
        ;

        $this->assertSame(['id' => 'new-game'], $this->service->create(self::TRAINER, 'demo', $roundCount));
    }

    /**
     * @return array<string, array{null|int, int}>
     */
    public static function providerCreateWithRoundCount(): array
    {
        return [
            'every Pokémon' => [null, 5],
            'fewer rounds' => [3, 3],
            'one round' => [1, 1],
            'as many rounds as Pokémon' => [5, 5],
            'more rounds than Pokémon' => [50, 5],
        ];
    }

    #[Test]
    public function createWithUnknownDex(): void
    {
        $this->repository
            ->method('getPlayableDex')
            ->willReturn(null)
        ;

        $this->repository
            ->expects($this->never())
            ->method('startGame')
        ;

        $this->expectException(ColorGameNotFoundException::class);
        $this->expectExceptionMessageIsOrContains('Dex douze can not be played');

        $this->service->create(self::TRAINER, 'douze');
    }

    #[Test]
    public function createWithoutPokemons(): void
    {
        $this->repository
            ->method('getPlayableDex')
            ->willReturn(['id' => 'dex-id', 'is_shiny' => false])
        ;

        $this->repository
            ->method('getPlayablePokemons')
            ->willReturn([])
        ;

        $this->repository
            ->expects($this->never())
            ->method('startGame')
        ;

        $this->expectException(InvalidColorGameRequestException::class);
        $this->expectExceptionMessageIsOrContains('Dex demo has no Pokémon to play with');

        $this->service->create(self::TRAINER, 'demo');
    }

    #[Test]
    public function get(): void
    {
        $game = ['id' => self::GAME_ID, 'is_shiny' => true];
        $round = ['position' => 2, 'pokemon_id' => 'p2'];
        $colorSets = [['position' => 1]];

        $this->repository
            ->expects($this->once())
            ->method('getGame')
            ->with(self::TRAINER, self::GAME_ID)
            ->willReturn($game)
        ;

        $this->repository
            ->expects($this->once())
            ->method('getNextRound')
            ->with(self::GAME_ID)
            ->willReturn($round)
        ;

        $this->repository
            ->expects($this->once())
            ->method('getColorSets')
            ->with('p2', true)
            ->willReturn($colorSets)
        ;

        $this->assertSame(
            [
                'game' => $game,
                'round' => $round,
                'color_sets' => $colorSets,
            ],
            $this->service->get(self::TRAINER, self::GAME_ID),
        );
    }

    #[Test]
    public function getFinishedGame(): void
    {
        $game = ['id' => self::GAME_ID, 'is_shiny' => false];

        $this->repository
            ->method('getGame')
            ->willReturn($game)
        ;

        $this->repository
            ->method('getNextRound')
            ->willReturn(null)
        ;

        $this->repository
            ->expects($this->never())
            ->method('getColorSets')
        ;

        $this->assertSame(
            [
                'game' => $game,
                'round' => null,
                'color_sets' => [],
            ],
            $this->service->get(self::TRAINER, self::GAME_ID),
        );
    }

    #[Test]
    public function getUnknownGame(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('getGame')
            ->willReturn(null)
        ;

        $this->repository
            ->expects($this->never())
            ->method('getNextRound')
        ;

        $this->expectException(ColorGameNotFoundException::class);
        $this->expectExceptionMessageIsOrContains('Game '.self::GAME_ID.' not found');

        $this->service->get(self::TRAINER, self::GAME_ID);
    }

    #[Test]
    public function answerRight(): void
    {
        $this->assertAnswer(choice: 2, choicesOrder: '3,2,1', isCorrect: true, correctChoice: 2);
    }

    #[Test]
    public function answerWrong(): void
    {
        $this->assertAnswer(choice: 0, choicesOrder: '3,2,1', isCorrect: false, correctChoice: 2);
    }

    #[Test]
    public function answerRightFirstChoice(): void
    {
        $this->assertAnswer(choice: 0, choicesOrder: '1,2', isCorrect: true, correctChoice: 0);
    }

    #[Test]
    public function answerFinishedGame(): void
    {
        $this->repository
            ->method('getGame')
            ->willReturn(['status' => 'completed'])
        ;

        $this->repository
            ->expects($this->never())
            ->method('answerRound')
        ;

        $this->expectException(ColorGameConflictException::class);
        $this->expectExceptionMessageIsOrContains('This game is over');

        $this->service->answer(self::TRAINER, self::GAME_ID, $this->getAnswer(1, 0));
    }

    #[Test]
    public function answerUnknownRound(): void
    {
        $this->repository
            ->method('getGame')
            ->willReturn(['status' => 'in_progress'])
        ;

        $this->repository
            ->expects($this->once())
            ->method('getRoundAt')
            ->with(self::GAME_ID, 4)
            ->willReturn(null)
        ;

        $this->expectException(ColorGameNotFoundException::class);
        $this->expectExceptionMessageIsOrContains('There is no round 4 in this game');

        $this->service->answer(self::TRAINER, self::GAME_ID, $this->getAnswer(4, 0));
    }

    #[Test]
    public function answerAlreadyAnsweredRound(): void
    {
        $this->repository
            ->method('getGame')
            ->willReturn(['status' => 'in_progress'])
        ;

        $this->repository
            ->method('getRoundAt')
            ->willReturn(['chosen_choice' => 0, 'choices_order' => '1,2'])
        ;

        $this->repository
            ->expects($this->never())
            ->method('answerRound')
        ;

        $this->expectException(ColorGameConflictException::class);
        $this->expectExceptionMessageIsOrContains('Round 1 has already been answered');

        $this->service->answer(self::TRAINER, self::GAME_ID, $this->getAnswer(1, 0));
    }

    #[Test]
    public function answerUnknownChoice(): void
    {
        $this->repository
            ->method('getGame')
            ->willReturn(['status' => 'in_progress'])
        ;

        $this->repository
            ->method('getRoundAt')
            ->willReturn(['chosen_choice' => null, 'choices_order' => '2,1'])
        ;

        $this->repository
            ->expects($this->never())
            ->method('answerRound')
        ;

        $this->expectException(InvalidColorGameRequestException::class);
        $this->expectExceptionMessageIsOrContains('Choice 2 does not exist');

        $this->service->answer(self::TRAINER, self::GAME_ID, $this->getAnswer(1, 2));
    }

    #[Test]
    public function answerConcurrently(): void
    {
        $this->repository
            ->method('getGame')
            ->willReturn(['status' => 'in_progress'])
        ;

        $this->repository
            ->method('getRoundAt')
            ->willReturn(['chosen_choice' => null, 'choices_order' => '2,1'])
        ;

        $this->repository
            ->expects($this->once())
            ->method('answerRound')
            ->willReturn(false)
        ;

        $this->expectException(ColorGameConflictException::class);
        $this->expectExceptionMessageIsOrContains('Round 3 has already been answered');

        $this->service->answer(self::TRAINER, self::GAME_ID, $this->getAnswer(3, 1));
    }

    private function assertAnswer(int $choice, string $choicesOrder, bool $isCorrect, int $correctChoice): void
    {
        $gameBefore = ['status' => 'in_progress', 'answered_count' => 0];
        $gameAfter = ['status' => 'in_progress', 'answered_count' => 1];

        $this->repository
            ->expects($this->exactly(2))
            ->method('getGame')
            ->with(self::TRAINER, self::GAME_ID)
            ->willReturn($gameBefore, $gameAfter)
        ;

        $this->repository
            ->expects($this->once())
            ->method('getRoundAt')
            ->with(self::GAME_ID, 2)
            ->willReturn(['chosen_choice' => null, 'choices_order' => $choicesOrder])
        ;

        $this->repository
            ->expects($this->once())
            ->method('answerRound')
            ->with(self::GAME_ID, 2, $choice, $isCorrect)
            ->willReturn(true)
        ;

        $this->assertSame(
            [
                'position' => 2,
                'is_correct' => $isCorrect,
                'chosen_choice' => $choice,
                'correct_choice' => $correctChoice,
                'game' => $gameAfter,
            ],
            $this->service->answer(self::TRAINER, self::GAME_ID, $this->getAnswer(2, $choice)),
        );
    }

    private function getAnswer(int $position, int $choice): ColorGameAnswer
    {
        return new ColorGameAnswer([
            'position' => $position,
            'choice' => $choice,
        ]);
    }
}

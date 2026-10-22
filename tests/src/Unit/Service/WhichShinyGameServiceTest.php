<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\DexQueryOptions;
use App\DTO\WhichShinyGameAnswer;
use App\Exception\InvalidWhichShinyGameRequestException;
use App\Exception\WhichShinyGameConflictException;
use App\Exception\WhichShinyGameNotFoundException;
use App\Repository\WhichShinyGameRepository;
use App\Service\WhichShinyGameService;
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
#[CoversClass(WhichShinyGameService::class)]
final class WhichShinyGameServiceTest extends TestCase
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';
    private const string GAME_ID = '01a15aac-cfd2-7085-bbc7-88594aa005dd';
    private const int SEED = 12;

    private MockObject&WhichShinyGameRepository $repository;
    private WhichShinyGameService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->repository = $this->createMock(WhichShinyGameRepository::class);
        $this->service = new WhichShinyGameService(
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
        $pokemonIds = ['p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8'];

        $randomizer = new Randomizer(new Xoshiro256StarStar(self::SEED));
        $expectedRounds = array_map(
            static fn (string $pokemonId): array => [
                'pokemon_id' => $pokemonId,
                'shiny_slot' => $randomizer->getInt(1, 2),
            ],
            $randomizer->shuffleArray($pokemonIds),
        );

        $this->repository
            ->expects($this->once())
            ->method('getPlayableDexId')
            ->with('demo')
            ->willReturn('dex-id')
        ;

        $this->repository
            ->expects($this->once())
            ->method('getPlayablePokemonIds')
            ->with('dex-id')
            ->willReturn($pokemonIds)
        ;

        $gameId = null;
        $this->repository
            ->expects($this->once())
            ->method('startGame')
            ->willReturnCallback(function (string $startedGameId, string $trainer, string $dexId, array $rounds) use (&$gameId, $expectedRounds): void {
                $gameId = $startedGameId;

                $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}$/', $startedGameId);
                $this->assertSame(self::TRAINER, $trainer);
                $this->assertSame('dex-id', $dexId);
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

        // Every Pokémon is played once, the shiny image is drawn in both slots
        $this->assertEqualsCanonicalizing($pokemonIds, array_column($expectedRounds, 'pokemon_id'));
        $this->assertEqualsCanonicalizing([1, 2], array_values(array_unique(array_column($expectedRounds, 'shiny_slot'))));
    }

    #[Test]
    #[DataProvider('providerCreateWithRoundCount')]
    public function createWithRoundCount(?int $roundCount, int $expectedCount): void
    {
        $pokemonIds = ['p1', 'p2', 'p3', 'p4', 'p5'];
        $shuffledIds = (new Randomizer(new Xoshiro256StarStar(self::SEED)))->shuffleArray($pokemonIds);

        $this->repository
            ->method('getPlayableDexId')
            ->willReturn('dex-id')
        ;

        $this->repository
            ->method('getPlayablePokemonIds')
            ->willReturn($pokemonIds)
        ;

        // The first Pokémon of the shuffled dex are played
        $this->repository
            ->expects($this->once())
            ->method('startGame')
            ->with($this->anything(), $this->anything(), $this->anything(), $this->callback(
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
            ->method('getPlayableDexId')
            ->willReturn(null)
        ;

        $this->repository
            ->expects($this->never())
            ->method('startGame')
        ;

        $this->expectException(WhichShinyGameNotFoundException::class);
        $this->expectExceptionMessageIsOrContains('Dex douze can not be played');

        $this->service->create(self::TRAINER, 'douze');
    }

    #[Test]
    public function createWithoutPokemons(): void
    {
        $this->repository
            ->method('getPlayableDexId')
            ->willReturn('dex-id')
        ;

        $this->repository
            ->method('getPlayablePokemonIds')
            ->willReturn([])
        ;

        $this->repository
            ->expects($this->never())
            ->method('startGame')
        ;

        $this->expectException(InvalidWhichShinyGameRequestException::class);
        $this->expectExceptionMessageIsOrContains('Dex demo has no Pokémon to play with');

        $this->service->create(self::TRAINER, 'demo');
    }

    #[Test]
    public function get(): void
    {
        $game = ['id' => self::GAME_ID];
        $round = ['position' => 2, 'pokemon_id' => 'p2', 'shiny_slot' => 2];

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

        $this->assertSame(
            [
                'game' => $game,
                'round' => $round,
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

        $this->expectException(WhichShinyGameNotFoundException::class);
        $this->expectExceptionMessageIsOrContains('Game '.self::GAME_ID.' not found');

        $this->service->get(self::TRAINER, self::GAME_ID);
    }

    #[Test]
    public function answerRightFirst(): void
    {
        $this->assertAnswer(chosenSlot: 1, shinySlot: 1, isCorrect: true);
    }

    #[Test]
    public function answerRightSecond(): void
    {
        $this->assertAnswer(chosenSlot: 2, shinySlot: 2, isCorrect: true);
    }

    #[Test]
    public function answerWrongFirst(): void
    {
        $this->assertAnswer(chosenSlot: 1, shinySlot: 2, isCorrect: false);
    }

    #[Test]
    public function answerWrongSecond(): void
    {
        $this->assertAnswer(chosenSlot: 2, shinySlot: 1, isCorrect: false);
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

        $this->expectException(WhichShinyGameConflictException::class);
        $this->expectExceptionMessageIsOrContains('This game is over');

        $this->service->answer(self::TRAINER, self::GAME_ID, $this->getAnswer(1, 1));
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

        $this->expectException(WhichShinyGameNotFoundException::class);
        $this->expectExceptionMessageIsOrContains('There is no round 4 in this game');

        $this->service->answer(self::TRAINER, self::GAME_ID, $this->getAnswer(4, 1));
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
            ->willReturn(['chosen_slot' => 1, 'shiny_slot' => 1])
        ;

        $this->repository
            ->expects($this->never())
            ->method('answerRound')
        ;

        $this->expectException(WhichShinyGameConflictException::class);
        $this->expectExceptionMessageIsOrContains('Round 1 has already been answered');

        $this->service->answer(self::TRAINER, self::GAME_ID, $this->getAnswer(1, 1));
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
            ->willReturn(['chosen_slot' => null, 'shiny_slot' => 2])
        ;

        $this->repository
            ->expects($this->once())
            ->method('answerRound')
            ->willReturn(false)
        ;

        $this->expectException(WhichShinyGameConflictException::class);
        $this->expectExceptionMessageIsOrContains('Round 3 has already been answered');

        $this->service->answer(self::TRAINER, self::GAME_ID, $this->getAnswer(3, 2));
    }

    private function assertAnswer(int $chosenSlot, int $shinySlot, bool $isCorrect): void
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
            ->willReturn(['chosen_slot' => null, 'shiny_slot' => $shinySlot])
        ;

        $this->repository
            ->expects($this->once())
            ->method('answerRound')
            ->with(self::GAME_ID, 2, $chosenSlot, $isCorrect)
            ->willReturn(true)
        ;

        $this->assertSame(
            [
                'position' => 2,
                'is_correct' => $isCorrect,
                'chosen_slot' => $chosenSlot,
                'shiny_slot' => $shinySlot,
                'game' => $gameAfter,
            ],
            $this->service->answer(self::TRAINER, self::GAME_ID, $this->getAnswer(2, $chosenSlot)),
        );
    }

    private function getAnswer(int $position, int $slot): WhichShinyGameAnswer
    {
        return new WhichShinyGameAnswer([
            'position' => $position,
            'slot' => $slot,
        ]);
    }
}

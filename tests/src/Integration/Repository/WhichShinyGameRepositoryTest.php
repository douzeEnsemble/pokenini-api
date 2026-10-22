<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\DexQueryOptions;
use App\Repository\WhichShinyGameRepository;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(WhichShinyGameRepository::class)]
final class WhichShinyGameRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[Test]
    public function getPlayableDexList(): void
    {
        $rows = $this->getRepository()->getPlayableDexList(new DexQueryOptions([
            'include_unreleased_dex' => true,
            'include_premium_dex' => true,
        ]));

        $this->assertSame(
            [
                'redgreenblueyellow' => 7,
                'demo' => 21,
                'homepogo' => 0,
            ],
            array_column($rows, 'playable_count', 'slug'),
        );
    }

    #[Test]
    public function getPlayableDexListExcludesDeletedPokemons(): void
    {
        $this->getConnection()->executeStatement("UPDATE pokemon SET deleted_at = NOW() WHERE slug = 'caterpie'");

        $rows = $this->getRepository()->getPlayableDexList(new DexQueryOptions());

        $this->assertSame(
            ['demo' => 20],
            array_column($rows, 'playable_count', 'slug'),
        );
    }

    #[Test]
    public function getInProgressGamesByDex(): void
    {
        $games = $this->getRepository()->getInProgressGamesByDex(self::TRAINER);

        $this->assertSame(['demo'], array_keys($games));
        $this->assertSame('in_progress', $games['demo']['status']);
        $this->assertSame(3, $games['demo']['round_count']);
    }

    #[Test]
    public function getPlayableDexId(): void
    {
        $repository = $this->getRepository();

        $dexId = $repository->getPlayableDexId('demo');

        $this->assertNotNull($dexId);
        $this->assertTrue(Uuid::isValid($dexId));

        $this->assertNull($repository->getPlayableDexId('homeshiny'));
        $this->assertNull($repository->getPlayableDexId('home'));
        $this->assertNull($repository->getPlayableDexId('spoon'));
        $this->assertNull($repository->getPlayableDexId('douze'));
    }

    #[Test]
    public function getPlayablePokemonIds(): void
    {
        $repository = $this->getRepository();

        $dexId = $repository->getPlayableDexId('redgreenblueyellow');
        $this->assertNotNull($dexId);

        $pokemonIds = $repository->getPlayablePokemonIds($dexId);

        $expected = array_values($this->getPokemonIds(['douze', 'bulbasaur', 'ivysaur', 'venusaur', 'caterpie', 'metapod', 'butterfree']));
        sort($expected);

        $this->assertSame($expected, $pokemonIds);
    }

    #[Test]
    public function startGame(): void
    {
        $repository = $this->getRepository();

        $dexId = $repository->getPlayableDexId('demo');
        $this->assertNotNull($dexId);

        $pokemonIds = $this->getPokemonIds(['venusaur', 'bulbasaur']);
        $gameId = (string) Uuid::v4();

        $repository->startGame(
            $gameId,
            self::TRAINER,
            $dexId,
            [
                ['pokemon_id' => $pokemonIds['venusaur'], 'shiny_slot' => 2],
                ['pokemon_id' => $pokemonIds['bulbasaur'], 'shiny_slot' => 1],
            ],
        );

        $game = $repository->getGame(self::TRAINER, $gameId);
        $this->assertNotNull($game);
        $this->assertSame('in_progress', $game['status']);
        $this->assertSame(2, $game['round_count']);
        $this->assertSame(0, $game['answered_count']);
        $this->assertSame(0, $game['correct_count']);

        $firstRound = $repository->getNextRound($gameId);
        $this->assertNotNull($firstRound);
        $this->assertSame(1, $firstRound['position']);
        $this->assertSame('venusaur', $firstRound['pokemon_slug']);
        $this->assertSame('venusaur', $firstRound['pokemon_icon']);
        $this->assertSame(2, $firstRound['shiny_slot']);
        $this->assertNull($firstRound['chosen_slot']);

        $secondRound = $repository->getRoundAt($gameId, 2);
        $this->assertNotNull($secondRound);
        $this->assertSame('bulbasaur', $secondRound['pokemon_slug']);
        $this->assertSame(1, $secondRound['shiny_slot']);
        $this->assertNull($secondRound['chosen_slot']);

        $this->assertNull($repository->getRoundAt($gameId, 3));

        // The previous game in progress is abandoned, the one of another trainer is untouched
        $this->assertSame([$gameId], array_column($repository->getInProgressGamesByDex(self::TRAINER), 'id'));
        $this->assertSame(['demo'], array_keys($repository->getInProgressGamesByDex('other_trainer')));
        $this->assertSame(
            ['in_progress', 'abandoned', 'completed'],
            array_column($repository->getGames(self::TRAINER), 'status'),
        );
    }

    #[Test]
    public function answerRound(): void
    {
        $repository = $this->getRepository();

        $gameId = $this->getInProgressGameId();

        $this->assertTrue($repository->answerRound($gameId, 2, 1, true));
        $this->assertFalse($repository->answerRound($gameId, 2, 2, false));

        $round = $repository->getRoundAt($gameId, 2);
        $this->assertNotNull($round);
        $this->assertSame(1, $round['chosen_slot']);
        $this->assertTrue($round['is_correct']);

        $game = $repository->getGame(self::TRAINER, $gameId);
        $this->assertNotNull($game);
        $this->assertSame('in_progress', $game['status']);
        $this->assertSame(2, $game['answered_count']);
        $this->assertSame(2, $game['correct_count']);
        $this->assertNull($game['ended_at']);

        $this->assertTrue($repository->answerRound($gameId, 3, 1, false));

        $game = $repository->getGame(self::TRAINER, $gameId);
        $this->assertNotNull($game);
        $this->assertSame('completed', $game['status']);
        $this->assertSame(3, $game['answered_count']);
        $this->assertSame(2, $game['correct_count']);
        $this->assertNotNull($game['ended_at']);

        $this->assertNull($repository->getNextRound($gameId));
    }

    #[Test]
    public function getGameOfAnotherTrainer(): void
    {
        $this->assertNull($this->getRepository()->getGame('other_trainer', $this->getInProgressGameId()));
    }

    /**
     * @param string[] $slugs
     *
     * @return array<string, string>
     */
    private function getPokemonIds(array $slugs): array
    {
        $ids = [];
        foreach ($slugs as $slug) {
            /** @var string $pokemonId */
            $pokemonId = $this->getConnection()->fetchOne('SELECT id FROM pokemon WHERE slug = ?', [$slug]);

            $ids[$slug] = $pokemonId;
        }

        return $ids;
    }

    private function getInProgressGameId(): string
    {
        /** @var string */
        return $this->getConnection()->fetchOne(
            'SELECT id FROM which_shiny_game WHERE trainer_external_id = ? AND status = ?',
            [self::TRAINER, 'in_progress'],
        );
    }

    private function getRepository(): WhichShinyGameRepository
    {
        /** @var WhichShinyGameRepository */
        return self::getContainer()->get(WhichShinyGameRepository::class);
    }

    private function getConnection(): Connection
    {
        /** @var Connection */
        return self::getContainer()->get(Connection::class);
    }
}

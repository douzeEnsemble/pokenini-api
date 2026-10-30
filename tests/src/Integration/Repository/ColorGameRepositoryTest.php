<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\DexQueryOptions;
use App\Repository\ColorGameRepository;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(ColorGameRepository::class)]
final class ColorGameRepositoryTest extends KernelTestCase
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
                'demo' => 4,
                'homeshiny' => 2,
                'redgreenblueyellow' => 4,
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
            [
                'demo' => 3,
                'homeshiny' => 1,
            ],
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
    public function getPlayableDex(): void
    {
        $repository = $this->getRepository();

        $dex = $repository->getPlayableDex('homeshiny');

        $this->assertNotNull($dex);
        $this->assertTrue($dex['is_shiny']);
        $this->assertTrue(Uuid::isValid($dex['id']));

        $this->assertNull($repository->getPlayableDex('home'));
        $this->assertNull($repository->getPlayableDex('douze'));
    }

    #[Test]
    public function getPlayablePokemons(): void
    {
        $repository = $this->getRepository();

        $demo = $repository->getPlayableDex('demo');
        $this->assertNotNull($demo);

        $pokemons = $repository->getPlayablePokemons($demo['id'], false);

        $this->assertSame(
            [
                'bulbasaur' => '1,2,3',
                'caterpie' => '1,2',
                'ivysaur' => '1,2',
                'venusaur' => '1,2',
            ],
            $this->bySlug($pokemons),
        );

        $this->assertSame(
            [
                'bulbasaur' => '1,2',
                'caterpie' => '1,2',
            ],
            $this->bySlug($repository->getPlayablePokemons($demo['id'], true)),
        );
    }

    #[Test]
    public function startGame(): void
    {
        $repository = $this->getRepository();

        $demo = $repository->getPlayableDex('demo');
        $this->assertNotNull($demo);

        $pokemonIds = $this->getPokemonIds(['venusaur', 'bulbasaur']);
        $gameId = (string) Uuid::v4();

        $repository->startGame(
            $gameId,
            self::TRAINER,
            $demo['id'],
            false,
            [
                ['pokemon_id' => $pokemonIds['venusaur'], 'choices_order' => [2, 1]],
                ['pokemon_id' => $pokemonIds['bulbasaur'], 'choices_order' => [3, 1, 2]],
            ],
        );

        $game = $repository->getGame(self::TRAINER, $gameId);
        $this->assertNotNull($game);
        $this->assertSame('in_progress', $game['status']);
        $this->assertSame(2, $game['round_count']);
        $this->assertSame(0, $game['answered_count']);
        $this->assertSame(0, $game['correct_count']);
        $this->assertFalse($game['is_shiny']);

        $firstRound = $repository->getNextRound($gameId);
        $this->assertNotNull($firstRound);
        $this->assertSame(1, $firstRound['position']);
        $this->assertSame('venusaur', $firstRound['pokemon_slug']);
        $this->assertSame('venusaur', $firstRound['pokemon_icon']);
        $this->assertSame('2,1', $firstRound['choices_order']);

        $secondRound = $repository->getRoundAt($gameId, 2);
        $this->assertNotNull($secondRound);
        $this->assertSame('bulbasaur', $secondRound['pokemon_slug']);
        $this->assertSame('3,1,2', $secondRound['choices_order']);
        $this->assertNull($secondRound['chosen_choice']);

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
        $this->assertFalse($repository->answerRound($gameId, 2, 0, false));

        $round = $repository->getRoundAt($gameId, 2);
        $this->assertNotNull($round);
        $this->assertSame(1, $round['chosen_choice']);
        $this->assertTrue($round['is_correct']);

        $game = $repository->getGame(self::TRAINER, $gameId);
        $this->assertNotNull($game);
        $this->assertSame('in_progress', $game['status']);
        $this->assertSame(2, $game['answered_count']);
        $this->assertSame(2, $game['correct_count']);
        $this->assertNull($game['ended_at']);

        $this->assertTrue($repository->answerRound($gameId, 3, 0, false));

        $game = $repository->getGame(self::TRAINER, $gameId);
        $this->assertNotNull($game);
        $this->assertSame('completed', $game['status']);
        $this->assertSame(3, $game['answered_count']);
        $this->assertSame(2, $game['correct_count']);
        $this->assertNotNull($game['ended_at']);

        $this->assertNull($repository->getNextRound($gameId));
    }

    #[Test]
    public function getAnsweredRounds(): void
    {
        $rows = $this->getRepository()->getAnsweredRounds($this->getCompletedGameId());

        $this->assertSame(
            [
                ['position' => 1, 'pokemon_slug' => 'bulbasaur', 'is_correct' => true, 'choices_order' => '1,2', 'chosen_choice' => 0],
                ['position' => 2, 'pokemon_slug' => 'caterpie', 'is_correct' => false, 'choices_order' => '1,2', 'chosen_choice' => 1],
            ],
            array_map(
                static fn (array $row): array => [
                    'position' => $row['position'],
                    'pokemon_slug' => $row['pokemon_slug'],
                    'is_correct' => $row['is_correct'],
                    'choices_order' => $row['choices_order'],
                    'chosen_choice' => $row['chosen_choice'],
                ],
                $rows,
            ),
        );
        $this->assertSame('bulbasaur', $rows[0]['pokemon_icon']);
    }

    #[Test]
    public function getAnsweredRoundsOfAGameInProgress(): void
    {
        $rows = $this->getRepository()->getAnsweredRounds($this->getInProgressGameId());

        $this->assertSame([1], array_column($rows, 'position'));
        $this->assertSame(['bulbasaur'], array_column($rows, 'pokemon_slug'));
        $this->assertSame([1], array_column($rows, 'chosen_choice'));
    }

    #[Test]
    public function getAnsweredRoundsColorSets(): void
    {
        $this->assertSame(
            [
                [1, 1, '#8CD2B4;#DCC864'],
                [1, 2, '#D28CAA;#64DCC8'],
                [2, 1, '#F8D030;#78C850'],
                [2, 2, '#3050F8;#C878F8'],
            ],
            array_map(
                static fn (array $row): array => [$row['round_position'], $row['position'], $row['codes']],
                $this->getRepository()->getAnsweredRoundsColorSets($this->getCompletedGameId()),
            ),
        );
    }

    #[Test]
    public function getAnsweredRoundsColorSetsOfAGameInProgress(): void
    {
        $rows = $this->getRepository()->getAnsweredRoundsColorSets($this->getInProgressGameId());

        $this->assertSame([1, 1, 1], array_column($rows, 'round_position'));
        $this->assertSame([1, 2, 3], array_column($rows, 'position'));
        $this->assertSame(['teal;light green;dark teal', 'pink;magenta;dark red', 'olive;green;dark olive'], array_column($rows, 'names'));
        $this->assertSame(['bleu vert;vert clair;bleu vert foncé', 'rose;magenta;rouge foncé', 'olive;vert;olive foncé'], array_column($rows, 'french_names'));
    }

    #[Test]
    public function getGameOfAnotherTrainer(): void
    {
        $this->assertNull($this->getRepository()->getGame('other_trainer', $this->getInProgressGameId()));
    }

    #[Test]
    public function getColorSets(): void
    {
        $pokemonIds = $this->getPokemonIds(['caterpie']);

        $this->assertSame(
            [
                [
                    'position' => 1,
                    'codes' => '#F8D030;#78C850',
                    'names' => 'yellow;green',
                    'french_names' => 'jaune;vert',
                ],
                [
                    'position' => 2,
                    'codes' => '#3050F8;#C878F8',
                    'names' => 'blue;lavender',
                    'french_names' => 'bleu;lavande',
                ],
            ],
            $this->getRepository()->getColorSets($pokemonIds['caterpie'], true),
        );
    }

    /**
     * @param list<array{pokemon_id: string, positions: string}> $pokemons
     *
     * @return array<string, string>
     */
    private function bySlug(array $pokemons): array
    {
        $slugs = array_flip($this->getPokemonIds(['bulbasaur', 'ivysaur', 'venusaur', 'caterpie']));

        $result = [];
        foreach ($pokemons as $pokemon) {
            $result[$slugs[$pokemon['pokemon_id']]] = $pokemon['positions'];
        }

        ksort($result);

        return $result;
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
            'SELECT id FROM color_game WHERE trainer_external_id = ? AND status = ?',
            [self::TRAINER, 'in_progress'],
        );
    }

    private function getCompletedGameId(): string
    {
        /** @var string */
        return $this->getConnection()->fetchOne(
            'SELECT id FROM color_game WHERE trainer_external_id = ? AND status = ?',
            [self::TRAINER, 'completed'],
        );
    }

    private function getRepository(): ColorGameRepository
    {
        /** @var ColorGameRepository */
        return self::getContainer()->get(ColorGameRepository::class);
    }

    private function getConnection(): Connection
    {
        /** @var Connection */
        return self::getContainer()->get(Connection::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\DexQueryOptions;
use App\Repository\GameLocalizedColumns;
use App\Repository\ShinyGameRepository;
use App\Repository\WhichShinyGameRepository;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GameLocalizedColumns::class)]
#[CoversClass(ShinyGameRepository::class)]
#[CoversClass(WhichShinyGameRepository::class)]
final class GameRepositoriesLocalizedTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    /**
     * @param class-string<ShinyGameRepository|WhichShinyGameRepository> $class
     */
    #[DataProvider('providerClasses')]
    #[Test]
    public function playableDexListInALocale(string $class): void
    {
        $repository = $this->getRepository($class);
        $options = new DexQueryOptions(['include_unreleased_dex' => true, 'include_premium_dex' => true]);

        $legacy = $repository->getPlayableDexList($options);
        $french = $repository->getPlayableDexList($options, 'fr');
        $english = $repository->getPlayableDexList($options, 'en');

        $this->assertNotEmpty($legacy);
        $this->assertSame(array_column($legacy, 'slug'), array_column($french, 'slug'));
        $this->assertSame(array_column($legacy, 'french_name'), array_column($french, 'name'));
        $this->assertSame(array_column($legacy, 'french_description'), array_column($french, 'description'));
        $this->assertSame(array_column($legacy, 'category_french_name'), array_column($french, 'category_name'));
        $this->assertSame(array_column($legacy, 'name'), array_column($english, 'name'));
        $this->assertSame(array_column($legacy, 'playable_count'), array_column($french, 'playable_count'));
        $this->assertAllNull(array_column($french, 'french_name'));
        $this->assertAllNull(array_column($french, 'french_description'));
        $this->assertAllNull(array_column($french, 'category_french_name'));
    }

    /**
     * @return array<string, array{class-string<ShinyGameRepository|WhichShinyGameRepository>}>
     */
    public static function providerClasses(): array
    {
        return [
            'shiny game' => [ShinyGameRepository::class],
            'which shiny game' => [WhichShinyGameRepository::class],
        ];
    }

    /**
     * @param class-string<ShinyGameRepository|WhichShinyGameRepository> $class
     */
    #[DataProvider('providerRepositories')]
    #[Test]
    public function gamesInALocale(string $class, string $table): void
    {
        $repository = $this->getRepository($class);
        $gameId = $this->getGameId($table, 'in_progress');

        $legacy = $repository->getGames(self::TRAINER);
        $french = $repository->getGames(self::TRAINER, 'fr');

        $this->assertNotEmpty($legacy);
        $this->assertSame(array_column($legacy, 'dex_french_name'), array_column($french, 'dex_name'));
        $this->assertAllNull(array_column($french, 'dex_french_name'));

        $game = $repository->getGame(self::TRAINER, $gameId, 'fr');
        $this->assertNotNull($game);
        $this->assertNull($game['dex_french_name']);
        $this->assertSame($repository->getGame(self::TRAINER, $gameId)['dex_french_name'] ?? null, $game['dex_name']);

        $inProgress = $repository->getInProgressGamesByDex(self::TRAINER, 'fr');
        $this->assertAllNull(array_column($inProgress, 'dex_french_name'));
        $this->assertSame(
            array_column($repository->getInProgressGamesByDex(self::TRAINER), 'dex_french_name'),
            array_column($inProgress, 'dex_name'),
        );
    }

    /**
     * @param class-string<ShinyGameRepository|WhichShinyGameRepository> $class
     */
    #[DataProvider('providerRepositories')]
    #[Test]
    public function roundsInALocale(string $class, string $table): void
    {
        $repository = $this->getRepository($class);
        $gameId = $this->getGameId($table, 'completed');

        $legacy = $repository->getAnsweredRounds($gameId);
        $french = $repository->getAnsweredRounds($gameId, 'fr');

        $this->assertNotEmpty($legacy);
        $this->assertSame(array_column($legacy, 'pokemon_french_name'), array_column($french, 'pokemon_name'));
        $this->assertSame(array_column($legacy, 'pokemon_simplified_french_name'), array_column($french, 'pokemon_simplified_name'));
        $this->assertSame(array_column($legacy, 'pokemon_forms_french_label'), array_column($french, 'pokemon_forms_label'));
        $this->assertAllNull(array_column($french, 'pokemon_french_name'));
        $this->assertAllNull(array_column($french, 'pokemon_simplified_french_name'));
        $this->assertAllNull(array_column($french, 'pokemon_forms_french_label'));

        $next = $repository->getNextRound($this->getGameId($table, 'in_progress'), 'fr');
        $this->assertNotNull($next);
        $this->assertNull($next['pokemon_french_name']);
        $this->assertNotNull($repository->getRoundAt($this->getGameId($table, 'in_progress'), 1, 'fr'));
    }

    /**
     * @return array<string, array{class-string<ShinyGameRepository|WhichShinyGameRepository>, string}>
     */
    public static function providerRepositories(): array
    {
        return [
            'shiny game' => [ShinyGameRepository::class, 'shiny_game'],
            'which shiny game' => [WhichShinyGameRepository::class, 'which_shiny_game'],
        ];
    }

    #[Test]
    public function parameters(): void
    {
        $this->assertSame([], GameLocalizedColumns::parameters(null, 'en'));
        $this->assertSame(['locale' => 'fr', 'fallback_locale' => 'en'], GameLocalizedColumns::parameters('fr', 'en'));
    }

    /**
     * @template T of ShinyGameRepository|WhichShinyGameRepository
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function getRepository(string $class): ShinyGameRepository|WhichShinyGameRepository
    {
        /** @var T */
        return self::getContainer()->get($class);
    }

    /**
     * @param list<mixed> $values
     */
    private function assertAllNull(array $values): void
    {
        $this->assertNotEmpty($values);
        $this->assertSame(array_fill(0, count($values), null), $values);
    }

    private function getGameId(string $table, string $status): string
    {
        $connection = self::getContainer()->get(Connection::class);

        /** @var string */
        return $connection->fetchOne(
            "SELECT id FROM {$table} WHERE trainer_external_id = ? AND status = ?",
            [self::TRAINER, $status],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\DexQueryOptions;
use App\Repository\ColorGameRepository;
use App\Repository\GameLocalizedColumns;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GameLocalizedColumns::class)]
#[CoversClass(ColorGameRepository::class)]
final class ColorGameRepositoryLocalizedTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[Test]
    public function playableDexListInALocale(): void
    {
        $options = new DexQueryOptions(['include_unreleased_dex' => true, 'include_premium_dex' => true]);

        $legacy = $this->getRepository()->getPlayableDexList($options);
        $french = $this->getRepository()->getPlayableDexList($options, 'fr');
        $english = $this->getRepository()->getPlayableDexList($options, 'en');

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

    #[Test]
    public function gamesInALocale(): void
    {
        $gameId = $this->getGameId('in_progress');

        $legacy = $this->getRepository()->getGames(self::TRAINER);
        $french = $this->getRepository()->getGames(self::TRAINER, 'fr');

        $this->assertNotEmpty($legacy);
        $this->assertSame(array_column($legacy, 'dex_french_name'), array_column($french, 'dex_name'));
        $this->assertAllNull(array_column($french, 'dex_french_name'));

        $game = $this->getRepository()->getGame(self::TRAINER, $gameId, 'fr');
        $this->assertNotNull($game);
        $this->assertNull($game['dex_french_name']);

        $inProgress = $this->getRepository()->getInProgressGamesByDex(self::TRAINER, 'fr');
        $this->assertAllNull(array_column($inProgress, 'dex_french_name'));
        $this->assertSame(
            array_column($this->getRepository()->getInProgressGamesByDex(self::TRAINER), 'dex_french_name'),
            array_column($inProgress, 'dex_name'),
        );
    }

    #[Test]
    public function roundsInALocale(): void
    {
        $gameId = $this->getGameId('completed');

        $legacy = $this->getRepository()->getAnsweredRounds($gameId);
        $french = $this->getRepository()->getAnsweredRounds($gameId, 'fr');

        $this->assertNotEmpty($legacy);
        $this->assertSame(array_column($legacy, 'pokemon_french_name'), array_column($french, 'pokemon_name'));
        $this->assertSame(array_column($legacy, 'pokemon_simplified_french_name'), array_column($french, 'pokemon_simplified_name'));
        $this->assertSame(array_column($legacy, 'pokemon_forms_french_label'), array_column($french, 'pokemon_forms_label'));
        $this->assertAllNull(array_column($french, 'pokemon_french_name'));
        $this->assertAllNull(array_column($french, 'pokemon_simplified_french_name'));
        $this->assertAllNull(array_column($french, 'pokemon_forms_french_label'));

        $inProgress = $this->getGameId('in_progress');
        $next = $this->getRepository()->getNextRound($inProgress, 'fr');
        $this->assertNotNull($next);
        $this->assertNull($next['pokemon_french_name']);
        $this->assertNotNull($this->getRepository()->getRoundAt($inProgress, 1, 'fr'));
    }

    #[Test]
    public function colorSetsInALocale(): void
    {
        $pokemonId = $this->getBulbasaurId();

        $legacy = $this->getRepository()->getColorSets($pokemonId, false);
        $french = $this->getRepository()->getColorSets($pokemonId, false, 'fr');
        $english = $this->getRepository()->getColorSets($pokemonId, false, 'en');

        $this->assertNotEmpty($legacy);
        $this->assertSame(array_column($legacy, 'french_names'), array_column($french, 'names'));
        $this->assertSame(array_column($legacy, 'names'), array_column($english, 'names'));
        $this->assertSame(array_column($legacy, 'codes'), array_column($french, 'codes'));
        $this->assertAllNull(array_column($french, 'french_names'));
    }

    #[Test]
    public function colorSetsInALanguageWithoutNamesUseTheFallbackLanguage(): void
    {
        $pokemonId = $this->getBulbasaurId();

        $legacy = $this->getRepository()->getColorSets($pokemonId, false);
        $german = $this->getRepository()->getColorSets($pokemonId, false, 'de', 'en');

        $this->assertSame(array_column($legacy, 'names'), array_column($german, 'names'));
    }

    #[Test]
    public function answeredRoundsColorSetsInALocale(): void
    {
        $gameId = $this->getGameId('completed');

        $legacy = $this->getRepository()->getAnsweredRoundsColorSets($gameId);
        $french = $this->getRepository()->getAnsweredRoundsColorSets($gameId, 'fr');

        $this->assertNotEmpty($legacy);
        $this->assertSame(array_column($legacy, 'french_names'), array_column($french, 'names'));
        $this->assertAllNull(array_column($french, 'french_names'));
    }

    /**
     * @param list<mixed> $values
     */
    private function assertAllNull(array $values): void
    {
        $this->assertNotEmpty($values);
        $this->assertSame(array_fill(0, count($values), null), $values);
    }

    private function getGameId(string $status): string
    {
        /** @var string */
        return $this->getConnection()->fetchOne(
            'SELECT id FROM color_game WHERE trainer_external_id = ? AND status = ?',
            [self::TRAINER, $status],
        );
    }

    private function getBulbasaurId(): string
    {
        /** @var string */
        return $this->getConnection()->fetchOne("SELECT id FROM pokemon WHERE slug = 'bulbasaur'");
    }

    private function getConnection(): Connection
    {
        return self::getContainer()->get(Connection::class);
    }

    private function getRepository(): ColorGameRepository
    {
        /** @var ColorGameRepository */
        return self::getContainer()->get(ColorGameRepository::class);
    }
}

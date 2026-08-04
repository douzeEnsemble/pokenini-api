<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\PokedexRepository;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(PokedexRepository::class)]
final class PokedexRepositoryUpsertIfDifferentTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    public function testReturnsTrueAndWritesWhenValueChanges(): void
    {
        $repository = self::getContainer()->get(PokedexRepository::class);
        $trainerDexId = $this->getTrainerDexId('goldsilvercrystal');

        // Fixture: ivysaur is 'no' in goldsilvercrystal for this trainer.
        $changed = $repository->upsertIfDifferent($trainerDexId, 'ivysaur', 'yes');

        $this->assertTrue($changed);
        $this->assertSame('yes', $this->getCatchStateSlug($trainerDexId, 'ivysaur'));
    }

    public function testReturnsFalseAndDoesNotWriteWhenValueIsUnchanged(): void
    {
        $repository = self::getContainer()->get(PokedexRepository::class);
        $trainerDexId = $this->getTrainerDexId('goldsilvercrystal');

        // Fixture: ivysaur is already 'no' in goldsilvercrystal for this trainer.
        $changed = $repository->upsertIfDifferent($trainerDexId, 'ivysaur', 'no');

        $this->assertFalse($changed);
        $this->assertSame('no', $this->getCatchStateSlug($trainerDexId, 'ivysaur'));
    }

    public function testReturnsFalseWhenPokemonNotInDex(): void
    {
        $repository = self::getContainer()->get(PokedexRepository::class);
        // Fixture: 'douze' has no dex_availability row for goldsilvercrystal (only redgreenblueyellow and home).
        $trainerDexId = $this->getTrainerDexId('goldsilvercrystal');

        $changed = $repository->upsertIfDifferent($trainerDexId, 'douze', 'yes');

        $this->assertFalse($changed);
        $this->assertNull($this->getCatchStateSlug($trainerDexId, 'douze'));
    }

    public function testCreatesAFreshPokedexRowWhenNoneExists(): void
    {
        $repository = self::getContainer()->get(PokedexRepository::class);
        // Fixture: 'douze' has a dex_availability row for redgreenblueyellow but no pokedex row for it yet.
        $trainerDexId = $this->getTrainerDexId('redgreenblueyellow');

        $before = $this->getCatchStateSlug($trainerDexId, 'douze');
        $this->assertNull($before);

        $changed = $repository->upsertIfDifferent($trainerDexId, 'douze', 'yes');

        $this->assertTrue($changed);
        $this->assertSame('yes', $this->getCatchStateSlug($trainerDexId, 'douze'));
    }

    private function getTrainerDexId(string $dexSlug): string
    {
        $connection = self::getContainer()->get(Connection::class);

        /** @var string */
        return $connection->executeQuery(
            'SELECT id FROM trainer_dex WHERE slug = :slug AND trainer_external_id = :trainer',
            ['slug' => $dexSlug, 'trainer' => self::TRAINER]
        )->fetchOne();
    }

    private function getCatchStateSlug(string $trainerDexId, string $pokemonSlug): ?string
    {
        $connection = self::getContainer()->get(Connection::class);

        /** @var false|string $result */
        $result = $connection->executeQuery(
            <<<'SQL'
                SELECT      cs.slug
                FROM        pokedex AS pd
                        JOIN pokemon AS p
                            ON pd.pokemon_id = p.id AND p.slug = :pokemon_slug
                        JOIN catch_state AS cs
                            ON pd.catch_state_id = cs.id
                WHERE       pd.trainer_dex_id = :trainer_dex_id
                SQL,
            ['trainer_dex_id' => $trainerDexId, 'pokemon_slug' => $pokemonSlug]
        )->fetchOne();

        return false === $result ? null : $result;
    }
}

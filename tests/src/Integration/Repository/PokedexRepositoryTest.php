<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\PokedexRepository;
use App\Repository\Trait\FiltersTrait;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(PokedexRepository::class)]
#[CoversTrait(FiltersTrait::class)]
final class PokedexRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use GetPokedexTrait;

    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testUpdate(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertEquals('Maybe', $pokedexBefore['name']);
        $this->assertEquals('maybe', $pokedexBefore['slug']);

        $repo = self::getContainer()->get(PokedexRepository::class);

        $repo->upsert('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'ivysaur', 'yes');

        $pokedexAfter = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertEquals('Maybe', $pokedexAfter['name']);
        $this->assertEquals('maybe', $pokedexAfter['slug']);
    }

    public function testInsert(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('goldsilvercrystal', 'douze');

        $this->assertEmpty($pokedexBefore);

        $repo = self::getContainer()->get(PokedexRepository::class);

        $repo->upsert('7b52009b64fd0a2a49e6d8a939753077792b0554', 'goldsilvercrystal', 'douze', 'maybenot');

        $pokedexAfter = $this->getPokedexFromSlugs('goldsilvercrystal', 'douze');

        $this->assertEquals('Maybe not', $pokedexAfter['name']);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);
    }

    public function testUpsertReturnsTheWrittenTrainerDexIdWhenCatchStateChanges(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);
        $trainerDexId = $this->getTrainerDexId('goldsilvercrystal');

        // Fixture: ivysaur is 'no' in goldsilvercrystal for this trainer, so 'yes' is an actual change.
        $result = $repo->upsert(self::TRAINER, 'goldsilvercrystal', 'ivysaur', 'yes');

        $this->assertSame($trainerDexId, $result);
    }

    public function testUpsertReturnsNullWhenCatchStateIsUnchanged(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        // Fixture: bulbasaur is already 'yes' in goldsilvercrystal for this trainer, so this is a no-op write.
        $result = $repo->upsert(self::TRAINER, 'goldsilvercrystal', 'bulbasaur', 'yes');

        $this->assertNull($result);
    }

    public function testUpsertReturnsNullWhenDexSlugDoesNotResolveForThisTrainer(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        // Fixture: 'demo' has no trainer_dex row for this trainer (only for the other test trainer),
        // so the trainer_dex_id subselect resolves to NULL.
        $result = $repo->upsert(self::TRAINER, 'demo', 'ivysaur', 'yes');

        $this->assertNull($result);
    }

    public function testGetDexUsage(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getDexUsage();

        $this->assertEquals(
            [
                [
                    'nb' => 5,
                    'slug' => 'redgreenblueyellow',
                    'name' => 'Red / Green / Blue / Yellow',
                    'french_name' => 'Rouge / Vert / Bleu / Jaune',
                ],
                [
                    'nb' => 5,
                    'slug' => 'goldsilvercrystal',
                    'name' => 'Gold / Silver / Crystal',
                    'french_name' => 'Or / Argent / Cristal',
                ],
                [
                    'nb' => 5,
                    'slug' => 'home',
                    'name' => 'Home',
                    'french_name' => 'Home',
                ],
                [
                    'nb' => 4,
                    'slug' => 'homepogo',
                    'name' => 'Home PoGo',
                    'french_name' => 'Home PoGo',
                ],
                [
                    'nb' => 3,
                    'slug' => 'rubysapphireemerald',
                    'name' => 'Ruby / Sapphire / Emerald',
                    'french_name' => 'Rubis / Saphir / Émeraude',
                ],
                [
                    'nb' => 3,
                    'slug' => 'homeshiny',
                    'name' => "Home\nShiny",
                    'french_name' => "Home\nChromatique",
                ],
                [
                    'nb' => 1,
                    'slug' => 'demo',
                    'name' => 'Demo',
                    'french_name' => 'Démo',
                ],
                [
                    'nb' => 1,
                    'slug' => 'rubysapphireemeraldshiny',
                    'name' => 'Ruby / Sapphire / Emerald: Shiny',
                    'french_name' => 'Rubis / Saphir / Émeraude: Chromatique',
                ],
            ],
            $counts
        );
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
}

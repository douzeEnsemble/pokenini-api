<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\PokedexRepository;
use App\Repository\Trait\FiltersTrait;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
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

        $this->assertEquals('Yes', $pokedexAfter['name']);
        $this->assertEquals('yes', $pokedexAfter['slug']);
    }

    public function testInsert(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'douze');

        $this->assertEmpty($pokedexBefore);

        $repo = self::getContainer()->get(PokedexRepository::class);

        $repo->upsert('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'douze', 'maybenot');

        $pokedexAfter = $this->getPokedexFromSlugs('redgreenblueyellow', 'douze');

        $this->assertEquals('Maybe not', $pokedexAfter['name']);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);
    }

    public function testGetDexUsage(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getDexUsage();

        $this->assertEquals(
            [
                [
                    'nb' => 2,
                    'slug' => 'redgreenblueyellow',
                    'name' => 'Red / Green / Blue / Yellow',
                    'french_name' => 'Rouge / Vert / Bleu / Jaune',
                ],
                [
                    'nb' => 2,
                    'slug' => 'goldsilvercrystal',
                    'name' => 'Gold / Silver / Crystal',
                    'french_name' => 'Or / Argent / Cristal',
                ],
                [
                    'nb' => 2,
                    'slug' => 'home',
                    'name' => 'Home',
                    'french_name' => 'Home',
                ],
                [
                    'nb' => 1,
                    'slug' => 'rubysapphireemerald',
                    'name' => 'Ruby / Sapphire / Emerald',
                    'french_name' => 'Rubis / Saphir / Émeraude',
                ],
                [
                    'nb' => 1,
                    'slug' => 'homeshiny',
                    'name' => "Home\nShiny",
                    'french_name' => "Home\nChromatique",
                ],
                [
                    'nb' => 1,
                    'slug' => 'homepogo',
                    'name' => 'Home PoGo',
                    'french_name' => 'Home PoGo',
                ],
            ],
            $counts
        );
    }
}

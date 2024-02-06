<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Album;

use App\Service\Album\AlbumPokemonService;
use App\Tests\Common\Data\AlbumData;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AlbumPokemonServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testListUser12RedGreenBlueYellow(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow');

        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowContent(
                'no',
                'maybe',
                'maybenot',
                'maybenot',
                null,
                null,
                null
            ),
            $pokemons
        );
    }

    public function testListUser12GoldSilverCrystal(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get('7b52009b64fd0a2a49e6d8a939753077792b0554', 'goldsilvercrystal');

        $this->assertEquals(
            AlbumData::getExpectedGoldSilverCrystalContent(
                'yes',
                'no',
                'no',
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
    }

    public function testListUser13(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'redgreenblueyellow');

        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowContent(
                'yes',
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
    }

    public function testListUserUnknown(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get('46546542313186', 'redgreenblueyellow');

        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowContent(
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service\Album;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Service\Album\AlbumPokemonService;
use App\Tests\Common\Data\AlbumData;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(AlbumPokemonService::class)]
final class AlbumPokemonServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function listUser12RedGreenBlueYellow(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'redgreenblueyellow',
            AlbumFilters::createFromArray([]),
        );

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

    #[Test]
    public function listUser12GoldSilverCrystal(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'goldsilvercrystal',
            AlbumFilters::createFromArray([]),
        );

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

    #[Test]
    public function listUser13(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            'bd307a3ec329e10a2cff8fb87480823da114f8f4',
            'redgreenblueyellow',
            AlbumFilters::createFromArray([]),
        );

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

    #[Test]
    public function listUserUnknown(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '46546542313186',
            'redgreenblueyellow',
            AlbumFilters::createFromArray([]),
        );

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

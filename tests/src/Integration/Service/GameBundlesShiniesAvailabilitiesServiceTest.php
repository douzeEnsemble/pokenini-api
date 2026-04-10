<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Pokemon;
use App\Repository\PokemonsRepository;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleShinyAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GameBundlesShiniesAvailabilitiesService::class)]
final class GameBundlesShiniesAvailabilitiesServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleShinyAvailabilityTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetFromPokemon(): void
    {
        /** @var GameBundlesShiniesAvailabilitiesService $service */
        $service = static::getContainer()->get(GameBundlesShiniesAvailabilitiesService::class);

        /** @var PokemonsRepository $pokemonsRepo */
        $pokemonsRepo = static::getContainer()->get(PokemonsRepository::class);

        /** @var Pokemon $pokemonDouze */
        $pokemonDouze = $pokemonsRepo->findOneBy(['name' => 'Douze']);

        $listDouze = $service->getFromPokemon($pokemonDouze);
        $this->assertNull($listDouze->redgreenblueyellow);
        $this->assertNull($listDouze->goldsilvercrystal);

        /** @var Pokemon $pokemonBulbasaur */
        $pokemonBulbasaur = $pokemonsRepo->findOneBy(['name' => 'Bulbasaur']);

        $listBulbasaur = $service->getFromPokemon($pokemonBulbasaur);
        $this->assertTrue($listBulbasaur->redgreenblueyellow);
        $this->assertTrue($listBulbasaur->goldsilvercrystal);

        /** @var Pokemon $pokemonMegaVenusaur */
        $pokemonMegaVenusaur = $pokemonsRepo->findOneBy(['name' => 'Mega Venusaur']);

        $listMegaVenusaur = $service->getFromPokemon($pokemonMegaVenusaur);
        $this->assertFalse($listMegaVenusaur->redgreenblueyellow);
        $this->assertFalse($listMegaVenusaur->goldsilvercrystal);
    }
}

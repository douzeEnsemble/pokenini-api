<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Pokemon;
use App\Repository\PokemonsRepository;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleShinyAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function getFromPokemon(): void
    {
        $service = self::getContainer()->get(GameBundlesShiniesAvailabilitiesService::class);

        $pokemonsRepo = self::getContainer()->get(PokemonsRepository::class);

        /** @var Pokemon $pokemonDouze */
        $pokemonDouze = $pokemonsRepo->findOneBy(['name' => 'Douze']);

        $listDouze = $service->getFromPokemon($pokemonDouze);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listDouze->redgreenblueyellow);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listDouze->goldsilvercrystal);

        /** @var Pokemon $pokemonBulbasaur */
        $pokemonBulbasaur = $pokemonsRepo->findOneBy(['name' => 'Bulbasaur']);

        $listBulbasaur = $service->getFromPokemon($pokemonBulbasaur);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listBulbasaur->redgreenblueyellow);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listBulbasaur->goldsilvercrystal);

        /** @var Pokemon $pokemonMegaVenusaur */
        $pokemonMegaVenusaur = $pokemonsRepo->findOneBy(['name' => 'Mega Venusaur']);

        $listMegaVenusaur = $service->getFromPokemon($pokemonMegaVenusaur);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($listMegaVenusaur->redgreenblueyellow);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($listMegaVenusaur->goldsilvercrystal);
    }
}

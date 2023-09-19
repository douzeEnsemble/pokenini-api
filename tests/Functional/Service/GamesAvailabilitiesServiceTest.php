<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service;

use App\Entity\Pokemon;
use App\Repository\PokemonsRepository;
use App\Service\GamesAvailabilitiesService;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GamesAvailabilitiesServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetFromPokemon(): void
    {
        /** @var GamesAvailabilitiesService $service */
        $service = static::getContainer()->get(GamesAvailabilitiesService::class);

        /** @var PokemonsRepository $pokemonsRepo */
        $pokemonsRepo = static::getContainer()->get(PokemonsRepository::class);

        /** @var Pokemon $pokemonDouze */
        $pokemonDouze = $pokemonsRepo->findOneBy(['name' => 'Douze']);

        $listDouze = $service->getFromPokemon($pokemonDouze);
        $this->assertTrue($listDouze->red);
        $this->assertTrue($listDouze->blue);
        $this->assertNull($listDouze->emerald);

        /** @var Pokemon $pokemonBulbasaur */
        $pokemonBulbasaur = $pokemonsRepo->findOneBy(['name' => 'Bulbasaur']);

        $listBulbasaur = $service->getFromPokemon($pokemonBulbasaur);
        $this->assertTrue($listBulbasaur->red);
        $this->assertTrue($listBulbasaur->blue);
        $this->assertNull($listBulbasaur->emerald);

        /** @var Pokemon $pokemonDeoxys */
        $pokemonDeoxys = $pokemonsRepo->findOneBy(['name' => 'Deoxys']);

        $listDeoxys = $service->getFromPokemon($pokemonDeoxys);
        $this->assertNull($listDeoxys->red);
        $this->assertTrue($listDeoxys->ruby);
        $this->assertTrue($listDeoxys->emerald);
    }
}

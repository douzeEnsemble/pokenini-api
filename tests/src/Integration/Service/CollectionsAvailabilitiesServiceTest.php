<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Pokemon;
use App\Repository\PokemonsRepository;
use App\Service\CollectionsAvailabilitiesService;
use App\Tests\Common\Traits\CounterTrait\CountCollectionAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(CollectionsAvailabilitiesService::class)]
final class CollectionsAvailabilitiesServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountCollectionAvailabilityTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function getFromPokemon(): void
    {
        $service = self::getContainer()->get(CollectionsAvailabilitiesService::class);

        $pokemonsRepo = self::getContainer()->get(PokemonsRepository::class);

        /** @var Pokemon $pokemonDouze */
        $pokemonDouze = $pokemonsRepo->findOneBy(['name' => 'Douze']);

        $listDouze = $service->getFromPokemon($pokemonDouze);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($listDouze->pogoshadow);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listDouze->pogodynamax);

        /** @var Pokemon $pokemonBulbasaur */
        $pokemonBulbasaur = $pokemonsRepo->findOneBy(['name' => 'Bulbasaur']);

        $listBulbasaur = $service->getFromPokemon($pokemonBulbasaur);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listBulbasaur->pogoshadow);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($listBulbasaur->pogodynamax);

        /** @var Pokemon $pokemonDeoxys */
        $pokemonDeoxys = $pokemonsRepo->findOneBy(['name' => 'Deoxys']);

        $listDeoxys = $service->getFromPokemon($pokemonDeoxys);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listDeoxys->pogoshadow);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listDeoxys->pogodynamax);
    }
}

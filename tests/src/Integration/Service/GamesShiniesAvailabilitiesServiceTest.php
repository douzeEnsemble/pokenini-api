<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Pokemon;
use App\Repository\PokemonsRepository;
use App\Service\GamesShiniesAvailabilitiesService;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GamesShiniesAvailabilitiesService::class)]
final class GamesShiniesAvailabilitiesServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function getFromPokemon(): void
    {
        $service = self::getContainer()->get(GamesShiniesAvailabilitiesService::class);

        $pokemonsRepo = self::getContainer()->get(PokemonsRepository::class);

        /** @var Pokemon $pokemonDouze */
        $pokemonDouze = $pokemonsRepo->findOneBy(['name' => 'Douze']);

        $listDouze = $service->getFromPokemon($pokemonDouze);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listDouze->red);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listDouze->blue);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listDouze->emerald);

        /** @var Pokemon $pokemonBulbasaur */
        $pokemonBulbasaur = $pokemonsRepo->findOneBy(['name' => 'Bulbasaur']);

        $listBulbasaur = $service->getFromPokemon($pokemonBulbasaur);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listBulbasaur->red);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listBulbasaur->blue);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listBulbasaur->emerald);

        /** @var Pokemon $pokemonDeoxys */
        $pokemonDeoxys = $pokemonsRepo->findOneBy(['name' => 'Deoxys']);

        $listDeoxys = $service->getFromPokemon($pokemonDeoxys);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listDeoxys->red);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listDeoxys->ruby);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($listDeoxys->emerald);
    }
}

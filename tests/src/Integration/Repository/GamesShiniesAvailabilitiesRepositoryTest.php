<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Pokemon;
use App\Repository\GamesShiniesAvailabilitiesRepository;
use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountGameShinyAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GamesShiniesAvailabilitiesRepository::class)]
final class GamesShiniesAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameShinyAvailabilityTrait;

    /** @var Pokemon[] */
    private array $pokemons;

    private GamesShiniesAvailabilitiesRepository $gamesShiniesAvailabilitiesRepo;
    private PokemonsRepository $pokemonsRepo;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();

        $this->gamesShiniesAvailabilitiesRepo = self::getContainer()->get(GamesShiniesAvailabilitiesRepository::class);
        $this->pokemonsRepo = self::getContainer()->get(PokemonsRepository::class);
    }

    public function testRemoveAll(): void
    {
        $this->assertGreaterThan(0, $this->getGameShinyAvailabilityCount());

        $queryBuilder = $this->gamesShiniesAvailabilitiesRepo->createQueryBuilder('gsa')
            ->delete()
        ;
        $queryBuilder->getQuery()->execute();

        $this->assertEquals(0, $this->getGameShinyAvailabilityCount());
    }

    public function testGetFromPokemon(): void
    {
        $pokemonDeoxys = $this->getPokemon('Deoxys');

        $listDeoxys = $this->gamesShiniesAvailabilitiesRepo->getFromPokemon($pokemonDeoxys);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listDeoxys->nexistepas);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listDeoxys->ruby);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($listDeoxys->emerald);
    }

    private function getPokemon(string $name): Pokemon
    {
        if (isset($this->pokemons[$name])) {
            return $this->pokemons[$name];
        }

        $pokemon = $this->pokemonsRepo->findOneBy(['name' => $name]);

        $this->assertNotNull($pokemon);

        $this->pokemons[$name] = $pokemon;

        return $pokemon;
    }
}

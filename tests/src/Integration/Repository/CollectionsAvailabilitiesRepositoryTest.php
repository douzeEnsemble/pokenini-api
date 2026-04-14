<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Pokemon;
use App\Repository\CollectionsAvailabilitiesRepository;
use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountCollectionAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(CollectionsAvailabilitiesRepository::class)]
final class CollectionsAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountCollectionAvailabilityTrait;

    /** @var Pokemon[] */
    private array $pokemons;

    private CollectionsAvailabilitiesRepository $collectionsAvailabilitiesRepo;
    private PokemonsRepository $pokemonsRepo;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();

        $this->collectionsAvailabilitiesRepo = self::getContainer()->get(CollectionsAvailabilitiesRepository::class);
        $this->pokemonsRepo = self::getContainer()->get(PokemonsRepository::class);
    }

    public function testRemoveAll(): void
    {
        $this->assertGreaterThan(0, $this->getCollectionAvailabilityCount());

        $queryBuilder = $this->collectionsAvailabilitiesRepo->createQueryBuilder('ca')
            ->delete()
        ;
        $queryBuilder->getQuery()->execute();

        $this->assertEquals(0, $this->getCollectionAvailabilityCount());
    }

    public function testGetFromPokemon(): void
    {
        $pokemonDouze = $this->getPokemon('Douze');

        $listDouze = $this->collectionsAvailabilitiesRepo->getFromPokemon($pokemonDouze);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listDouze->nexistepas);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listDouze->pogodynamax);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($listDouze->pogoshadow);

        $pokemonBulbasaur = $this->getPokemon('Bulbasaur');

        $listBulbasaur = $this->collectionsAvailabilitiesRepo->getFromPokemon($pokemonBulbasaur);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listBulbasaur->nexistepas);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($listBulbasaur->pogodynamax);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listBulbasaur->pogoshadow);
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

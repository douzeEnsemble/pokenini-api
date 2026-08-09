<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Pokemon;
use App\Repository\GamesAvailabilitiesRepository;
use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountGameAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GamesAvailabilitiesRepository::class)]
final class GamesAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameAvailabilityTrait;

    /** @var Pokemon[] */
    private array $pokemons;

    private GamesAvailabilitiesRepository $gamesAvailabilitiesRepo;
    private PokemonsRepository $pokemonsRepo;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();

        $this->gamesAvailabilitiesRepo = self::getContainer()->get(GamesAvailabilitiesRepository::class);
        $this->pokemonsRepo = self::getContainer()->get(PokemonsRepository::class);
    }

    #[Test]
    public function removeAll(): void
    {
        $this->assertGreaterThan(0, $this->getGameAvailabilityCount());

        $queryBuilder = $this->gamesAvailabilitiesRepo->createQueryBuilder('ga')
            ->delete()
        ;
        $queryBuilder->getQuery()->execute();

        $this->assertEquals(0, $this->getGameAvailabilityCount());
    }

    #[Test]
    public function getFromPokemon(): void
    {
        $pokemonDouze = $this->getPokemon('Douze');

        $listDouze = $this->gamesAvailabilitiesRepo->getFromPokemon($pokemonDouze);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listDouze->nexistepas);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listDouze->red);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($listDouze->green);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listDouze->blue);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($listDouze->yellow);

        $pokemonBulbasaur = $this->getPokemon('Bulbasaur');

        $listBulbasaur = $this->gamesAvailabilitiesRepo->getFromPokemon($pokemonBulbasaur);

        /** @phpstan-ignore property.notFound */
        $this->assertNull($listBulbasaur->nexistepas);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listBulbasaur->red);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listBulbasaur->green);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listBulbasaur->blue);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($listBulbasaur->yellow);
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

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\PokemonAvailabilitiesRepository;
use App\Tests\Common\Traits\CounterTrait\CountPokemonAvailabilitiesTrait;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(PokemonAvailabilitiesRepository::class)]
final class PokemonAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountPokemonAvailabilitiesTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function removeAllByCategory(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonAvailabilitiesCount('game_bundle'));
        $previousCount = $this->getPokemonAvailabilitiesCount('game_bundle_shiny');

        $repo = self::getContainer()->get(PokemonAvailabilitiesRepository::class);

        $repo->removeAllByCategory('game_bundle');

        $this->assertEquals(0, $this->getPokemonAvailabilitiesCount('game_bundle'));
        $this->assertEquals(
            $previousCount,
            $this->getPokemonAvailabilitiesCount('game_bundle_shiny')
        );
    }

    #[Test]
    public function calculateGameBundle(): void
    {
        $repo = self::getContainer()->get(PokemonAvailabilitiesRepository::class);

        // Clean the database
        $repo->removeAllByCategory('game_bundle');

        $previousCount = $this->getPokemonAvailabilitiesCount('game_bundle_shiny');

        $count = $repo->calculateGameBundle();

        $this->assertGreaterThan(0, $count);
        $this->assertEquals($count, $this->getPokemonAvailabilitiesCount('game_bundle'));
        $this->assertEquals(
            $previousCount,
            $this->getPokemonAvailabilitiesCount('game_bundle_shiny')
        );
    }

    #[Test]
    public function calculateGameBundleShiny(): void
    {
        $repo = self::getContainer()->get(PokemonAvailabilitiesRepository::class);

        // Clean the database
        $repo->removeAllByCategory('game_bundle_shiny');

        $previousCount = $this->getPokemonAvailabilitiesCount('game_bundle');

        $count = $repo->calculateGameBundleShiny();

        $this->assertGreaterThan(0, $count);
        $this->assertEquals($count, $this->getPokemonAvailabilitiesCount('game_bundle_shiny'));
        $this->assertEquals(
            $previousCount,
            $this->getPokemonAvailabilitiesCount('game_bundle')
        );
    }

    #[Test]
    public function calculateUnicity(): void
    {
        $repo = self::getContainer()->get(PokemonAvailabilitiesRepository::class);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->expectExceptionMessageMatches('/duplicate key value violates unique constraint/');

        $repo->calculateGameBundle();
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\GameBundle;
use App\Entity\GameBundleShinyAvailability;
use App\Entity\Pokemon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleShinyAvailability::class)]
final class GameBundleShinyAvailabilityTest extends TestCase
{
    #[Test]
    public function createAvailable(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'Douze';

        $gameBundle = new GameBundle();
        $gameBundle->slug = 'Tic,Tac';

        $gameBundleShinyAvailability = GameBundleShinyAvailability::create(
            $pokemon,
            $gameBundle,
            true
        );

        $this->assertEquals($pokemon, $gameBundleShinyAvailability->pokemon);
        $this->assertEquals($gameBundle, $gameBundleShinyAvailability->bundle);
        $this->assertTrue($gameBundleShinyAvailability->isAvailable);
    }

    #[Test]
    public function createUnavailable(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'Douze';

        $gameBundle = new GameBundle();
        $gameBundle->slug = 'Tic,Tac';

        $gameBundleShinyAvailability = GameBundleShinyAvailability::create(
            $pokemon,
            $gameBundle,
            false
        );

        $this->assertEquals($pokemon, $gameBundleShinyAvailability->pokemon);
        $this->assertEquals($gameBundle, $gameBundleShinyAvailability->bundle);
        $this->assertFalse($gameBundleShinyAvailability->isAvailable);
    }

    #[Test]
    public function getIdentifierDefault(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'Douze';

        $gameBundle = new GameBundle();
        $gameBundle->slug = 'Tic,Tac';

        $gameBundleShinyAvailability = GameBundleShinyAvailability::create(
            $pokemon,
            $gameBundle,
            true
        );

        $this->assertNull($gameBundleShinyAvailability->getIdentifier());
    }
}

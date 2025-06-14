<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\GameBundle;
use App\Entity\GameBundleShinyAvailability;
use App\Entity\Pokemon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleShinyAvailability::class)]
class GameBundleShinyAvailabilityTest extends TestCase
{
    public function testCreateAvailable(): void
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

    public function testCreateUnavailable(): void
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

    public function testGetIdentifierDefault(): void
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

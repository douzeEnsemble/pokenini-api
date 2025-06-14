<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\GameBundle;
use App\Entity\GameBundleAvailability;
use App\Entity\Pokemon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleAvailability::class)]
class GameBundleAvailabilityTest extends TestCase
{
    public function testCreateAvailable(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'Douze';

        $gameBundle = new GameBundle();
        $gameBundle->slug = 'Tic,Tac';

        $gameBundleAvailability = GameBundleAvailability::create(
            $pokemon,
            $gameBundle,
            true
        );

        $this->assertEquals($pokemon, $gameBundleAvailability->pokemon);
        $this->assertEquals($gameBundle, $gameBundleAvailability->bundle);
        $this->assertTrue($gameBundleAvailability->isAvailable);
    }

    public function testCreateUnavailable(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'Douze';

        $gameBundle = new GameBundle();
        $gameBundle->slug = 'Tic,Tac';

        $gameBundleAvailability = GameBundleAvailability::create(
            $pokemon,
            $gameBundle,
            false
        );

        $this->assertEquals($pokemon, $gameBundleAvailability->pokemon);
        $this->assertEquals($gameBundle, $gameBundleAvailability->bundle);
        $this->assertFalse($gameBundleAvailability->isAvailable);
    }

    public function testGetIdentifierDefault(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'Douze';

        $gameBundle = new GameBundle();
        $gameBundle->slug = 'Tic,Tac';

        $gameBundleAvailability = GameBundleAvailability::create(
            $pokemon,
            $gameBundle,
            true
        );

        $this->assertNull($gameBundleAvailability->getIdentifier());
    }
}

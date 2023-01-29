<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\GameBundle;
use App\Entity\GameBundleAvailability;
use App\Entity\Pokemon;
use PHPUnit\Framework\TestCase;

class GameBundleAvailabilityTest extends TestCase
{
    public function testCreateAvailable(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'Douze';

        $gameBundle = new GameBundle();
        $gameBundle->slug = 'Tic,Tac';

        $gameBundleAvailabitliy = GameBundleAvailability::create(
            $pokemon,
            $gameBundle,
            true
        );

        $this->assertEquals($pokemon, $gameBundleAvailabitliy->pokemon);
        $this->assertEquals($gameBundle, $gameBundleAvailabitliy->bundle);
        $this->assertTrue($gameBundleAvailabitliy->isAvailable);
    }

    public function testCreateUnavailable(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'Douze';

        $gameBundle = new GameBundle();
        $gameBundle->slug = 'Tic,Tac';

        $gameBundleAvailabitliy = GameBundleAvailability::create(
            $pokemon,
            $gameBundle,
            false
        );

        $this->assertEquals($pokemon, $gameBundleAvailabitliy->pokemon);
        $this->assertEquals($gameBundle, $gameBundleAvailabitliy->bundle);
        $this->assertFalse($gameBundleAvailabitliy->isAvailable);
    }
}

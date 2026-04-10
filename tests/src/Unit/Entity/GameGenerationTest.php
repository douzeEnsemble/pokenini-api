<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\GameGeneration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameGeneration::class)]
final class GameGenerationTest extends TestCase
{
    public function testGetNumber(): void
    {
        $generation = new GameGeneration();
        $generation->name = '12';

        $this->assertSame(12, $generation->getNumber());
    }

    public function testGetIdentifierDefault(): void
    {
        $generation = new GameGeneration();

        $this->assertNull($generation->getIdentifier());
    }

    public function testGetIdentifier(): void
    {
        $generation = new GameGeneration();
        $generation->name = 'Douze';

        $this->assertEquals('Douze', (string) $generation);
        $this->assertEquals('Douze', $generation->__toString());
    }
}

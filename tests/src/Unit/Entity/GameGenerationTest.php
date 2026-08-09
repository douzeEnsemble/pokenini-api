<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\GameGeneration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameGeneration::class)]
final class GameGenerationTest extends TestCase
{
    #[Test]
    public function getNumber(): void
    {
        $generation = new GameGeneration();
        $generation->name = '12';

        $this->assertSame(12, $generation->getNumber());
    }

    #[Test]
    public function getIdentifierDefault(): void
    {
        $generation = new GameGeneration();

        $this->assertNull($generation->getIdentifier());
    }

    #[Test]
    public function getIdentifier(): void
    {
        $generation = new GameGeneration();
        $generation->name = 'Douze';

        $this->assertEquals('Douze', (string) $generation);
        $this->assertEquals('Douze', $generation->__toString());
    }
}

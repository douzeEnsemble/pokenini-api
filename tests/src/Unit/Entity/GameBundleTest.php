<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\GameBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundle::class)]
final class GameBundleTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new GameBundle();

        $this->assertNull($entity->getIdentifier());
    }

    #[Test]
    public function convertToString(): void
    {
        $entity = new GameBundle();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}

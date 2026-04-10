<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\GameAvailability;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameAvailability::class)]
final class GameAvailabilityTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new GameAvailability();

        $this->assertNull($entity->getIdentifier());
    }
}

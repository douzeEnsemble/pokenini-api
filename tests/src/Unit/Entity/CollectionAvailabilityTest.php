<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\CollectionAvailability;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CollectionAvailability::class)]
final class CollectionAvailabilityTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new CollectionAvailability();

        $this->assertNull($entity->getIdentifier());
    }
}

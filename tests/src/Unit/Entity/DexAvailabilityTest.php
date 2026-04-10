<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\DexAvailability;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexAvailability::class)]
final class DexAvailabilityTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new DexAvailability();

        $this->assertNull($entity->getIdentifier());
    }
}

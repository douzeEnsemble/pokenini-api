<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Traits;

use App\Entity\Pokemon;
use App\Entity\Traits\SoftDeleteable;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversTrait(SoftDeleteable::class)]
final class SoftDeleteableTest extends TestCase
{
    public function testGetDeletedAtReturnsNullByDefault(): void
    {
        $entity = new Pokemon();

        $this->assertNull($entity->getDeletedAt());
    }

    public function testGetDeletedAtReflectsDeletedAtProperty(): void
    {
        $entity = new Pokemon();
        $date = new \DateTime('2026-01-01');
        $entity->deletedAt = $date;

        $this->assertSame($date, $entity->getDeletedAt());
    }
}

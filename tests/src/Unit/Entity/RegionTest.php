<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Region;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Region::class)]
final class RegionTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new Region();

        $this->assertNull($entity->getIdentifier());
    }

    #[Test]
    public function convertToString(): void
    {
        $entity = new Region();
        $entity->name = 'Douze';

        $this->assertSame('Douze', (string) $entity);
        $this->assertSame('Douze', $entity->__toString());
    }
}

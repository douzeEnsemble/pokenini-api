<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Region;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Region::class)]
class RegionTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new Region();

        $this->assertNull($entity->getIdentifier());
    }

    public function testConvertToString(): void
    {
        $entity = new Region();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}

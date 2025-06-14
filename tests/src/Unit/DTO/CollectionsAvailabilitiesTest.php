<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\CollectionsAvailabilities;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CollectionsAvailabilities::class)]
class CollectionsAvailabilitiesTest extends TestCase
{
    public function testGet(): void
    {
        $object = new CollectionsAvailabilities([
            'a' => true,
            'b' => false,
        ]);

        $this->assertTrue($object->a);
        $this->assertFalse($object->b);
    }

    public function testSet(): void
    {
        $this->expectException(\Exception::class);

        $object = new CollectionsAvailabilities([]);

        $object->c = true;
    }

    public function testIsset(): void
    {
        $object = new CollectionsAvailabilities([
            'a' => true,
        ]);

        $this->assertTrue(isset($object->a));
        $this->assertFalse(isset($object->b));
    }

    public function testAll(): void
    {
        $object = new CollectionsAvailabilities([
            'a' => true,
            'b' => false,
        ]);

        $this->assertEquals(
            [
                'a' => true,
                'b' => false,
            ],
            $object->all()
        );
    }
}

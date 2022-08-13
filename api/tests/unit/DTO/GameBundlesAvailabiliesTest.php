<?php

namespace App\Tests\Unit\DTO;

use App\DTO\GameBundlesAvailabilities;
use PHPUnit\Framework\TestCase;

class GameBundlesAvailabiliesTest extends TestCase
{
    public function testGet(): void
    {
        $object = new GameBundlesAvailabilities([
            'a' => true,
            'b' => false,
        ]);

        $this->assertTrue($object->a);
        $this->assertFalse($object->b);
    }

    public function testSet(): void
    {
        $this->expectException(\Exception::class);

        $object = new GameBundlesAvailabilities([]);

        $object->c = true;
    }

    public function testIsset(): void
    {
        $object = new GameBundlesAvailabilities([
            'a' => true,
        ]);

        $this->assertTrue(isset($object->a));
        $this->assertFalse(isset($object->b));
    }
}

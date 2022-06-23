<?php

namespace App\Tests\unit\DTO;

use App\DTO\GameBundlesAvailabilities;

class GameBundlesAvailabiliesTest extends \PHPUnit\Framework\TestCase
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

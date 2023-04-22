<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\GameBundlesShiniesAvailabilities;
use PHPUnit\Framework\TestCase;

class GameBundlesShiniesAvailabilitiesTest extends TestCase
{
    public function testGet(): void
    {
        $object = new GameBundlesShiniesAvailabilities([
            'a' => true,
            'b' => false,
        ]);

        $this->assertTrue($object->a);
        $this->assertFalse($object->b);
    }

    public function testSet(): void
    {
        $this->expectException(\Exception::class);

        $object = new GameBundlesShiniesAvailabilities([]);

        $object->c = true;
    }

    public function testIsset(): void
    {
        $object = new GameBundlesShiniesAvailabilities([
            'a' => true,
        ]);

        $this->assertTrue(isset($object->a));
        $this->assertFalse(isset($object->b));
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\GamesShiniesAvailabilities;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GamesShiniesAvailabilities::class)]
final class GamesShiniesAvailabilitiesTest extends TestCase
{
    public function testGet(): void
    {
        $object = new GamesShiniesAvailabilities([
            'a' => true,
            'b' => false,
        ]);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($object->a);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($object->b);
    }

    public function testSet(): void
    {
        $this->expectException(\Exception::class);

        $object = new GamesShiniesAvailabilities([]);

        /** @phpstan-ignore property.notFound */
        $object->c = true;
    }

    public function testIsset(): void
    {
        $object = new GamesShiniesAvailabilities([
            'a' => true,
        ]);

        $this->assertTrue(isset($object->a));
        $this->assertFalse(isset($object->b));
    }

    public function testAll(): void
    {
        $object = new GamesShiniesAvailabilities([
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

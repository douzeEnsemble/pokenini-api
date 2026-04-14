<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\GameBundlesShiniesAvailabilities;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundlesShiniesAvailabilities::class)]
final class GameBundlesShiniesAvailabilitiesTest extends TestCase
{
    public function testGet(): void
    {
        $object = new GameBundlesShiniesAvailabilities([
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

        $object = new GameBundlesShiniesAvailabilities([]);

        /** @phpstan-ignore property.notFound */
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

    public function testAll(): void
    {
        $object = new GameBundlesShiniesAvailabilities([
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

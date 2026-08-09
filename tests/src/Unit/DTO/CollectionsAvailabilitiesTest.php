<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\CollectionsAvailabilities;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CollectionsAvailabilities::class)]
final class CollectionsAvailabilitiesTest extends TestCase
{
    #[Test]
    public function get(): void
    {
        $object = new CollectionsAvailabilities([
            'a' => true,
            'b' => false,
        ]);

        /** @phpstan-ignore property.notFound */
        $this->assertTrue($object->a);

        /** @phpstan-ignore property.notFound */
        $this->assertFalse($object->b);
    }

    #[Test]
    public function set(): void
    {
        $this->expectException(\Exception::class);

        $object = new CollectionsAvailabilities([]);

        /** @phpstan-ignore property.notFound */
        $object->c = true;
    }

    #[Test]
    public function isset(): void
    {
        $object = new CollectionsAvailabilities([
            'a' => true,
        ]);

        $this->assertTrue(isset($object->a));
        $this->assertFalse(isset($object->b));
    }

    #[Test]
    public function all(): void
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

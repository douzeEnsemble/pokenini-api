<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\GamesAvailabilities;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GamesAvailabilities::class)]
final class GamesAvailabilitiesTest extends TestCase
{
    #[Test]
    public function get(): void
    {
        $object = new GamesAvailabilities([
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

        $object = new GamesAvailabilities([]);

        /** @phpstan-ignore property.notFound */
        $object->c = true;
    }

    #[Test]
    public function isset(): void
    {
        $object = new GamesAvailabilities([
            'a' => true,
        ]);

        $this->assertTrue(isset($object->a));
        $this->assertFalse(isset($object->b));
    }

    #[Test]
    public function all(): void
    {
        $object = new GamesAvailabilities([
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

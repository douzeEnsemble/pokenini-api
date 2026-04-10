<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\AlbumFilter;

use App\DTO\AlbumFilter\AlbumFilterValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumFilterValue::class)]
final class AlbumFilterValueTest extends TestCase
{
    public function testConstruct(): void
    {
        $albumFilterValue = new AlbumFilterValue('douze');

        $this->assertEquals('douze', $albumFilterValue->value);
    }
}

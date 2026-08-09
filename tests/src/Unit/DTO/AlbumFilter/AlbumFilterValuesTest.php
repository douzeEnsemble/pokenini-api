<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\AlbumFilter;

use App\DTO\AlbumFilter\AlbumFilterValues;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumFilterValues::class)]
final class AlbumFilterValuesTest extends TestCase
{
    #[Test]
    public function construct(): void
    {
        $albumFilterValues = new AlbumFilterValues(['douze', 'treize', '!quatorze']);

        $this->assertCount(2, $albumFilterValues->values);
        $this->assertCount(1, $albumFilterValues->negativeValues);

        $this->assertEquals('douze', $albumFilterValues->values[0]->value);
        $this->assertEquals('treize', $albumFilterValues->values[1]->value);
        $this->assertEquals('quatorze', $albumFilterValues->negativeValues[0]->value);
    }

    #[Test]
    public function extract(): void
    {
        $albumFilterValues = new AlbumFilterValues(['douze', 'treize', '!quatorze']);

        $this->assertEquals(
            ['douze', 'treize'],
            $albumFilterValues->extract()
        );
    }

    #[Test]
    public function extractNegatives(): void
    {
        $albumFilterValues = new AlbumFilterValues(['douze', 'treize', '!quatorze']);

        $this->assertEquals(
            ['quatorze'],
            $albumFilterValues->extractNegatives()
        );
    }

    #[Test]
    public function extractManyNegatives(): void
    {
        $albumFilterValues = new AlbumFilterValues(['!douze', '!treize', '!quatorze']);

        $this->assertEquals(
            ['douze', 'treize', 'quatorze'],
            $albumFilterValues->extractNegatives()
        );
    }

    #[Test]
    public function hasNull(): void
    {
        $albumFilterValues = new AlbumFilterValues(['douze', null, 'treize']);

        $this->assertTrue($albumFilterValues->hasNull());
    }

    #[Test]
    public function hasNullFirst(): void
    {
        $albumFilterValues = new AlbumFilterValues([null, 'douze']);

        $this->assertTrue($albumFilterValues->hasNull());
    }

    #[Test]
    public function hasNullLast(): void
    {
        $albumFilterValues = new AlbumFilterValues(['douze', null]);

        $this->assertTrue($albumFilterValues->hasNull());
    }

    #[Test]
    public function hasNullFalse(): void
    {
        $albumFilterValues = new AlbumFilterValues(['douze', 'treize']);

        $this->assertFalse($albumFilterValues->hasNull());
    }

    #[Test]
    public function hasNullEmpty(): void
    {
        $albumFilterValues = new AlbumFilterValues([]);

        $this->assertFalse($albumFilterValues->hasNull());
    }
}

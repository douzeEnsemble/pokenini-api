<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\AlbumFilter\AlbumFilterValues;
use PHPUnit\Framework\TestCase;

class AlbumFilterValuesTest extends TestCase
{
    public function testConstruct(): void
    {
        $albumFilterValues = new AlbumFilterValues(['douze', 'treize']);

        $this->assertCount(2, $albumFilterValues->values);

        $this->assertEquals('douze', $albumFilterValues->values[0]->value);
        $this->assertEquals('treize', $albumFilterValues->values[1]->value);
    }

    public function testExtract(): void
    {
        $albumFilterValues = new AlbumFilterValues(['douze', 'treize']);

        $this->assertEquals(
            ['douze', 'treize'],
            $albumFilterValues->extract()
        );
    }

    public function testHasNull(): void
    {
        $albumFilterValues = new AlbumFilterValues(['douze', null, 'treize']);

        $this->assertTrue($albumFilterValues->hasNull());
    }

    public function testHasNullFirst(): void
    {
        $albumFilterValues = new AlbumFilterValues([null, 'douze']);

        $this->assertTrue($albumFilterValues->hasNull());
    }

    public function testHasNullLast(): void
    {
        $albumFilterValues = new AlbumFilterValues(['douze', null]);

        $this->assertTrue($albumFilterValues->hasNull());
    }

    public function testHasNullFalse(): void
    {
        $albumFilterValues = new AlbumFilterValues(['douze', 'treize']);

        $this->assertFalse($albumFilterValues->hasNull());
    }

    public function testHasNullEmpty(): void
    {
        $albumFilterValues = new AlbumFilterValues([]);

        $this->assertFalse($albumFilterValues->hasNull());
    }
}

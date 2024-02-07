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
}

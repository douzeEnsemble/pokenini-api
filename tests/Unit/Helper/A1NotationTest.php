<?php

declare(strict_types=1);

namespace App\Tests\Unit\Helper;

use App\Helper\A1Notation;
use PHPUnit\Framework\TestCase;

class A1NotationTest extends TestCase
{
    /**
     * @dataProvider indexToLetterProvider
     */
    public function testIndexToLetter(int $index, string $expected): void
    {
        $this->assertEquals($expected, A1Notation::indexToLetter($index));
    }

    /**
     * @dataProvider indexToA1NotationProvider
     */
    public function testFromIndex(int $rowIndex, int $columnIndex, string $expected): void
    {
        $this->assertEquals($expected, A1Notation::fromIndex($rowIndex, $columnIndex));
    }

    /**
     * @return int[][]|string[][]
     */
    public function indexToLetterProvider(): array
    {
        return [
            [
                'index' => 0,
                'expected' => 'A',
            ],
            [
                'index' => 2,
                'expected' => 'C',
            ],
            [
                'index' => 27,
                'expected' => 'AB',
            ],
            [
                'index' => 53,
                'expected' => 'BB',
            ],
        ];
    }

    /**
     * @return int[][]|string[][]
     */
    public function indexToA1NotationProvider(): array
    {
        return [
            [
                'row' => 0,
                'col' => 0,
                'expected' => 'A1',
            ],
            [
                'row' => 2,
                'col' => 0,
                'expected' => 'A3',
            ],
            [
                'row' => 2,
                'col' => 2,
                'expected' => 'C3',
            ],
            [
                'row' => 2,
                'col' => 27,
                'expected' => 'AB3',
            ],
            [
                'row' => 2,
                'col' => 53,
                'expected' => 'BB3',
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Helper;

use App\Helper\ColorSetsParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ColorSetsParser::class)]
final class ColorSetsParserTest extends TestCase
{
    #[Test]
    public function parse(): void
    {
        $this->assertSame(
            [
                [
                    'codes' => '#F08030;#E05020;#F8D070',
                    'names' => 'orange;red orange;light yellow',
                    'french_names' => 'orange;rouge orangé;jaune clair',
                ],
                [
                    'codes' => '#B030F0;#8020E0;#70F8D0',
                    'names' => 'purple;violet;turquoise',
                    'french_names' => 'violet;violet foncé;turquoise',
                ],
            ],
            ColorSetsParser::parse(
                '#F08030;#E05020;#F8D070 | #B030F0;#8020E0;#70F8D0',
                'orange;red orange;light yellow | purple;violet;turquoise',
                'orange;rouge orangé;jaune clair | violet;violet foncé;turquoise',
            ),
        );
    }

    #[Test]
    public function parseTrimsSpaces(): void
    {
        $this->assertSame(
            [
                [
                    'codes' => '#F08030;#E05020',
                    'names' => 'orange;red orange',
                    'french_names' => 'orange;rouge orangé',
                ],
                [
                    'codes' => '#B030F0;#8020E0',
                    'names' => 'purple;violet',
                    'french_names' => 'violet;violet foncé',
                ],
            ],
            ColorSetsParser::parse(
                ' #F08030 ; #E05020|#B030F0;#8020E0 ',
                'orange ;red orange|  purple; violet',
                'orange; rouge orangé |violet ;violet foncé',
            ),
        );
    }

    #[Test]
    #[DataProvider('providerEmpty')]
    public function parseEmpty(string $codes, string $names, string $frenchNames): void
    {
        $this->assertSame([], ColorSetsParser::parse($codes, $names, $frenchNames));
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function providerEmpty(): array
    {
        return [
            'empty' => ['', '', ''],
            'blank' => [' ', '  ', ' '],
            'no value' => ['-', '-', '-'],
            'no value with spaces' => [' - ', '-', ' -'],
            'mixed' => ['', '-', ''],
        ];
    }

    #[Test]
    #[DataProvider('providerInvalid')]
    public function parseInvalid(string $codes, string $names, string $frenchNames, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains($message);

        ColorSetsParser::parse($codes, $names, $frenchNames);
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function providerInvalid(): array
    {
        return [
            'only one set' => [
                '#F08030;#E05020',
                'orange;red orange',
                'orange;rouge orangé',
                'At least 2 sets are expected, 1 given',
            ],
            'no codes' => [
                '-',
                'orange | red',
                'orange | rouge',
                'At least 2 sets are expected, 0 given',
            ],
            'missing names set' => [
                '#F08030 | #E05020',
                'orange',
                'orange | rouge',
                'Codes, names and french names must have the same number of sets',
            ],
            'missing french names set' => [
                '#F08030 | #E05020',
                'orange | red',
                'orange',
                'Codes, names and french names must have the same number of sets',
            ],
            'missing name colour' => [
                '#F08030;#000000 | #E05020;#000000',
                'orange;black | red',
                'orange;noir | rouge;noir',
                'Codes, names and french names sets must have the same number of colours',
            ],
            'missing french name colour' => [
                '#F08030;#000000 | #E05020;#000000',
                'orange;black | red;black',
                'orange;noir | rouge',
                'Codes, names and french names sets must have the same number of colours',
            ],
            'empty code' => [
                '#F08030; | #E05020;#000000',
                'orange;black | red;black',
                'orange;noir | rouge;noir',
                'A colour can not be empty',
            ],
            'empty name' => [
                '#F08030;#000000 | #E05020;#000000',
                'orange;black | red;',
                'orange;noir | rouge;noir',
                'A colour can not be empty',
            ],
            'empty french name' => [
                '#F08030;#000000 | #E05020;#000000',
                'orange;black | red;black',
                ';noir | rouge;noir',
                'A colour can not be empty',
            ],
            'empty last french name' => [
                '#F08030;#000000 | #E05020;#000000',
                'orange;black | red;black',
                'orange; | rouge;noir',
                'A colour can not be empty',
            ],
            'only names' => [
                '-',
                'orange | red',
                '-',
                'At least 2 sets are expected, 0 given',
            ],
            'no french names' => [
                '#F08030 | #E05020',
                'orange | red',
                '-',
                'Codes, names and french names must have the same number of sets',
            ],
        ];
    }
}

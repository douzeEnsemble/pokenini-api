<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Factory\OptionalText;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(OptionalText::class)]
final class OptionalTextTest extends TestCase
{
    #[DataProvider('providerValues')]
    #[Test]
    public function fromScalar(bool|float|int|string|null $value, ?string $expected): void
    {
        $this->assertSame($expected, OptionalText::fromScalar($value));
    }

    /**
     * @return array<string, array{null|bool|float|int|string, null|string}>
     */
    public static function providerValues(): array
    {
        return [
            'null' => [null, null],
            'a text' => ['Bulbizarre', 'Bulbizarre'],
            'an empty text' => ['', ''],
            'a number' => [25, '25'],
        ];
    }
}

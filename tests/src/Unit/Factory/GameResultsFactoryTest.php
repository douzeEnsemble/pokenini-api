<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Factory\GameResultsFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameResultsFactory::class)]
final class GameResultsFactoryTest extends TestCase
{
    /**
     * @param list<bool> $expected
     */
    #[Test]
    #[DataProvider('providerFromString')]
    public function fromString(string $results, array $expected): void
    {
        $this->assertSame($expected, GameResultsFactory::fromString($results));
    }

    /**
     * @return array<string, array{string, list<bool>}>
     */
    public static function providerFromString(): array
    {
        return [
            'no answer' => ['', []],
            'one correct' => ['1', [true]],
            'one wrong' => ['0', [false]],
            'mixed' => ['1101', [true, true, false, true]],
        ];
    }
}

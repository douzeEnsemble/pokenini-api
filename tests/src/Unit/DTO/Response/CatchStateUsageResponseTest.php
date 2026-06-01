<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\CatchStateUsageResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CatchStateUsageResponse::class)]
final class CatchStateUsageResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new CatchStateUsageResponse(
            count: 11,
            name: 'No',
            frenchName: 'Non',
            color: '#e57373',
        );

        self::assertSame(11, $response->count);
        self::assertSame('No', $response->name);
        self::assertSame('Non', $response->frenchName);
        self::assertSame('#e57373', $response->color);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new CatchStateUsageResponse(
            count: 4,
            name: 'Maybe',
            frenchName: 'Peut être',
            color: 'blue',
        );

        self::assertSame(4, $response->count);
        self::assertSame('Maybe', $response->name);
        self::assertSame('Peut être', $response->frenchName);
        self::assertSame('blue', $response->color);
    }
}

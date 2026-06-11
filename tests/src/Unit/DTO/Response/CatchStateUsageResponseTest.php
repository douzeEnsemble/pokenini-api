<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\ReportCatchStateResponse;
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
        $catchState = new ReportCatchStateResponse(name: 'No', frenchName: 'Non', color: '#e57373');
        $response = new CatchStateUsageResponse(
            count: 11,
            catchState: $catchState,
        );

        self::assertSame(11, $response->count);
        self::assertSame($catchState, $response->catchState);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $catchState = new ReportCatchStateResponse(name: 'Maybe', frenchName: 'Peut être', color: 'blue');
        $response = new CatchStateUsageResponse(
            count: 4,
            catchState: $catchState,
        );

        self::assertSame(4, $response->count);
        self::assertSame($catchState, $response->catchState);
    }
}

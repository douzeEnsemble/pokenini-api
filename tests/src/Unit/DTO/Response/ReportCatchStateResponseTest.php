<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ReportCatchStateResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportCatchStateResponse::class)]
final class ReportCatchStateResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ReportCatchStateResponse(
            slug: 'no',
            name: 'No',
            frenchName: 'Non',
            color: '#e57373',
        );

        self::assertSame('no', $response->slug);
        self::assertSame('No', $response->name);
        self::assertSame('Non', $response->frenchName);
        self::assertSame('#e57373', $response->color);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ReportCatchStateResponse(
            slug: 'maybe',
            name: 'Maybe',
            frenchName: 'Peut être',
            color: 'blue',
        );

        self::assertSame('maybe', $response->slug);
        self::assertSame('Maybe', $response->name);
        self::assertSame('Peut être', $response->frenchName);
        self::assertSame('blue', $response->color);
    }
}

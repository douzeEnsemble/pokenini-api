<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumReportResponse;
use App\DTO\Response\AlbumReportStatisticResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumReportResponse::class)]
final class AlbumReportResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $statistic = new AlbumReportStatisticResponse(
            slug: 'yes',
            name: 'Yes',
            frenchName: 'Oui',
            count: 5,
        );
        $response = new AlbumReportResponse(
            total: 10,
            totalCaught: 5,
            totalUncaught: 3,
            detail: [$statistic],
        );

        self::assertSame(10, $response->total);
        self::assertSame(5, $response->totalCaught);
        self::assertSame(3, $response->totalUncaught);
        self::assertSame([$statistic], $response->detail);
    }

    #[Test]
    public function constructorAcceptsEmptyDetail(): void
    {
        $response = new AlbumReportResponse(
            total: 0,
            totalCaught: 0,
            totalUncaught: 0,
            detail: [],
        );

        self::assertSame(0, $response->total);
        self::assertSame(0, $response->totalCaught);
        self::assertSame(0, $response->totalUncaught);
        self::assertSame([], $response->detail);
    }
}

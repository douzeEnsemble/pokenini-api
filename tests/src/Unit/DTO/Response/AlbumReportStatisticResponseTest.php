<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumReportStatisticResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumReportStatisticResponse::class)]
final class AlbumReportStatisticResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $catchState = new AlbumCatchStateResponse(slug: 'yes', name: 'Yes', frenchName: 'Oui', color: '#e57373');
        $response = new AlbumReportStatisticResponse(
            catchState: $catchState,
            count: 7,
        );

        self::assertSame($catchState, $response->catchState);
        self::assertSame(7, $response->count);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $catchState = new AlbumCatchStateResponse(slug: 'no', name: 'No', frenchName: 'Non', color: '#e57373');
        $response = new AlbumReportStatisticResponse(
            catchState: $catchState,
            count: 3,
        );

        self::assertSame($catchState, $response->catchState);
        self::assertSame(3, $response->count);
    }
}

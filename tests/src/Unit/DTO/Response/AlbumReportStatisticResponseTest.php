<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

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
        $response = new AlbumReportStatisticResponse(
            slug: 'yes',
            name: 'Yes',
            frenchName: 'Oui',
            count: 7,
        );

        self::assertSame('yes', $response->slug);
        self::assertSame('Yes', $response->name);
        self::assertSame('Oui', $response->frenchName);
        self::assertSame(7, $response->count);
    }

    #[Test]
    public function constructorAcceptsOtherValues(): void
    {
        $response = new AlbumReportStatisticResponse(
            slug: 'no',
            name: 'No',
            frenchName: 'Non',
            count: 3,
        );

        self::assertSame('no', $response->slug);
        self::assertSame('No', $response->name);
        self::assertSame('Non', $response->frenchName);
        self::assertSame(3, $response->count);
    }
}

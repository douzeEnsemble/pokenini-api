<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\DexUsageResponse;
use App\DTO\Response\ReportCatchStateResponse;
use App\DTO\Response\ReportDexResponse;
use App\DTO\Response\ReportResponse;
use App\DTO\Response\ReportTrainerResponse;
use App\DTO\Response\TrainerCatchStateCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportResponse::class)]
final class ReportResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $counts = [new TrainerCatchStateCountResponse(
            count: 28,
            trainer: new ReportTrainerResponse(externalId: 'abc'),
        )];
        $dexUsage = [new DexUsageResponse(
            count: 2,
            dex: new ReportDexResponse(name: 'Home', frenchName: 'Home'),
        )];
        $catchStateUsage = [new CatchStateUsageResponse(
            count: 11,
            catchState: new ReportCatchStateResponse(name: 'No', frenchName: 'Non', color: '#e57373'),
        )];

        $response = new ReportResponse(
            catchStateCountsDefinedByTrainer: $counts,
            dexUsage: $dexUsage,
            catchStateUsage: $catchStateUsage,
        );

        self::assertSame($counts, $response->catchStateCountsDefinedByTrainer);
        self::assertSame($dexUsage, $response->dexUsage);
        self::assertSame($catchStateUsage, $response->catchStateUsage);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ReportResponse(
            catchStateCountsDefinedByTrainer: [],
            dexUsage: [],
            catchStateUsage: [],
        );

        self::assertSame([], $response->catchStateCountsDefinedByTrainer);
        self::assertSame([], $response->dexUsage);
        self::assertSame([], $response->catchStateUsage);
    }
}

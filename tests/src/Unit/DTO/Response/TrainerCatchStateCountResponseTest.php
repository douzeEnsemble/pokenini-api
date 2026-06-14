<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TrainerCatchStateCountResponse;
use App\DTO\Response\TrainerExternalIdResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerCatchStateCountResponse::class)]
final class TrainerCatchStateCountResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $trainer = new TrainerExternalIdResponse(externalId: '7b52009b64fd0a2a49e6d8a939753077792b0554');
        $response = new TrainerCatchStateCountResponse(
            count: 28,
            trainer: $trainer,
        );

        self::assertSame(28, $response->count);
        self::assertSame($trainer, $response->trainer);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $trainer = new TrainerExternalIdResponse(externalId: 'bd307a3ec329e10a2cff8fb87480823da114f8f4');
        $response = new TrainerCatchStateCountResponse(
            count: 3,
            trainer: $trainer,
        );

        self::assertSame(3, $response->count);
        self::assertSame($trainer, $response->trainer);
    }
}

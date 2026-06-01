<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TrainerCatchStateCountResponse;
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
        $response = new TrainerCatchStateCountResponse(
            count: 28,
            trainer: '7b52009b64fd0a2a49e6d8a939753077792b0554',
        );

        self::assertSame(28, $response->count);
        self::assertSame('7b52009b64fd0a2a49e6d8a939753077792b0554', $response->trainer);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new TrainerCatchStateCountResponse(
            count: 3,
            trainer: 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
        );

        self::assertSame(3, $response->count);
        self::assertSame('bd307a3ec329e10a2cff8fb87480823da114f8f4', $response->trainer);
    }
}

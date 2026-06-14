<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TrainerExternalIdResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerExternalIdResponse::class)]
final class TrainerExternalIdResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new TrainerExternalIdResponse(
            externalId: '7b52009b64fd0a2a49e6d8a939753077792b0554',
        );

        self::assertSame('7b52009b64fd0a2a49e6d8a939753077792b0554', $response->externalId);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new TrainerExternalIdResponse(
            externalId: 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
        );

        self::assertSame('bd307a3ec329e10a2cff8fb87480823da114f8f4', $response->externalId);
    }
}

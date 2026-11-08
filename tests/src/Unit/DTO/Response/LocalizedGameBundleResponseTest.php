<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GenerationResponse;
use App\DTO\Response\LocalizedGameBundleResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedGameBundleResponse::class)]
final class LocalizedGameBundleResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new LocalizedGameBundleResponse(slug: 'rgby', name: 'Rouge, Vert', generation: new GenerationResponse('1'));

        self::assertSame('rgby', $response->slug);
        self::assertSame('Rouge, Vert', $response->name);
        self::assertSame('1', $response->generation->slug);
    }
}

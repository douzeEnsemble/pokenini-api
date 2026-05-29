<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameBundleResponse;
use App\DTO\Response\GenerationResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleResponse::class)]
final class GameBundleResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new GameBundleResponse(
            slug: 'redgreenblueyellow',
            name: 'Red, Green, Blue, Yellow',
            frenchName: 'Rouge, Vert, Bleu, Jaune',
            generation: new GenerationResponse(slug: '1'),
        );

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('Red, Green, Blue, Yellow', $response->name);
        self::assertSame('Rouge, Vert, Bleu, Jaune', $response->frenchName);
        self::assertSame('1', $response->generation->slug);
    }
}

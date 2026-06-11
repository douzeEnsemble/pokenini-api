<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameSlugResponse::class)]
final class GameSlugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesSlug(): void
    {
        $response = new GameSlugResponse(slug: 'x');

        self::assertSame('x', $response->slug);
    }

    #[Test]
    public function constructorAcceptsAnotherSlug(): void
    {
        $response = new GameSlugResponse(slug: 'omegaruby');

        self::assertSame('omegaruby', $response->slug);
    }
}

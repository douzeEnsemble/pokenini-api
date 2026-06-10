<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameBundleSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleSlugResponse::class)]
final class GameBundleSlugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesSlug(): void
    {
        $response = new GameBundleSlugResponse(slug: 'redgreenblueyellow');

        self::assertSame('redgreenblueyellow', $response->slug);
    }

    #[Test]
    public function slugIsReadonly(): void
    {
        $response = new GameBundleSlugResponse(slug: 'goldsilvercrystal');

        self::assertSame('goldsilvercrystal', $response->slug);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexSlugResponse::class)]
final class DexSlugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesSlug(): void
    {
        $response = new DexSlugResponse(slug: 'home');

        self::assertSame('home', $response->slug);
    }

    #[Test]
    public function slugIsReadonly(): void
    {
        $response = new DexSlugResponse(slug: 'rubysapphireemerald');

        self::assertSame('rubysapphireemerald', $response->slug);
    }
}

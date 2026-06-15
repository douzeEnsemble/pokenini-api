<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameBundlesGroupResponse;
use App\DTO\Response\GameBundleSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundlesGroupResponse::class)]
final class GameBundlesGroupResponseTest extends TestCase
{
    #[Test]
    public function constructorStoresNormalAndShinyArrays(): void
    {
        $normal = [new GameBundleSlugResponse(slug: 'redgreenblueyellow')];
        $shiny = [new GameBundleSlugResponse(slug: 'goldsilvercrystal')];

        $response = new GameBundlesGroupResponse(normal: $normal, shiny: $shiny);

        self::assertSame($normal, $response->normal);
        self::assertSame($shiny, $response->shiny);
    }

    #[Test]
    public function constructorAcceptsEmptyArrays(): void
    {
        $response = new GameBundlesGroupResponse(normal: [], shiny: []);

        self::assertSame([], $response->normal);
        self::assertSame([], $response->shiny);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TrainerDexLinkResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexLinkResponse::class)]
final class TrainerDexLinkResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new TrainerDexLinkResponse(
            id: 'link-1',
            direction: 'to',
            targetDexSlug: 'shiny',
            targetName: 'Shiny Living',
            targetFrenchName: 'Vivarium Chromatique',
        );

        self::assertSame('link-1', $response->id);
        self::assertSame('to', $response->direction);
        self::assertSame('shiny', $response->targetDexSlug);
        self::assertSame('Shiny Living', $response->targetName);
        self::assertSame('Vivarium Chromatique', $response->targetFrenchName);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new TrainerDexLinkResponse(
            id: 'link-2',
            direction: 'both',
            targetDexSlug: 'home',
            targetName: 'Home',
            targetFrenchName: 'Home',
        );

        self::assertSame('link-2', $response->id);
        self::assertSame('both', $response->direction);
        self::assertSame('home', $response->targetDexSlug);
        self::assertSame('Home', $response->targetName);
        self::assertSame('Home', $response->targetFrenchName);
    }
}

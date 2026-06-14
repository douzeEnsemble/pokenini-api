<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ReportDexResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportDexResponse::class)]
final class ReportDexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ReportDexResponse(
            slug: 'home',
            name: 'Home',
            frenchName: 'Home',
        );

        self::assertSame('home', $response->slug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ReportDexResponse(
            slug: 'rubysapphireemerald',
            name: 'Ruby / Sapphire / Emerald',
            frenchName: 'Rubis / Saphir / Émeraude',
        );

        self::assertSame('rubysapphireemerald', $response->slug);
        self::assertSame('Ruby / Sapphire / Emerald', $response->name);
        self::assertSame('Rubis / Saphir / Émeraude', $response->frenchName);
    }
}

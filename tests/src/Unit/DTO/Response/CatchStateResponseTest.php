<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\CatchStateResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CatchStateResponse::class)]
final class CatchStateResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new CatchStateResponse(
            slug: 'no',
            name: 'No',
            frenchName: 'Non',
            color: '#e57373',
        );

        self::assertSame('no', $response->slug);
        self::assertSame('No', $response->name);
        self::assertSame('Non', $response->frenchName);
        self::assertSame('#e57373', $response->color);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new CatchStateResponse(
            slug: 'yes',
            name: 'Yes',
            frenchName: 'Oui',
            color: '#66bb6a',
        );

        self::assertSame('yes', $response->slug);
        self::assertSame('Yes', $response->name);
        self::assertSame('Oui', $response->frenchName);
        self::assertSame('#66bb6a', $response->color);
    }
}

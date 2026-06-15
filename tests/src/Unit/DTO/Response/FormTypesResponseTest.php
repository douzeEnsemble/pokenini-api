<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormResponse;
use App\DTO\Response\FormTypesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FormTypesResponse::class)]
final class FormTypesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllFourProperties(): void
    {
        $category = [new FormResponse('slug1', 'name1', 'nom1')];
        $regional = [new FormResponse('slug2', 'name2', 'nom2')];
        $special = [new FormResponse('slug3', 'name3', 'nom3')];
        $variant = [new FormResponse('slug4', 'name4', 'nom4')];

        $response = new FormTypesResponse($category, $regional, $special, $variant);

        self::assertSame($category, $response->category);
        self::assertSame($regional, $response->regional);
        self::assertSame($special, $response->special);
        self::assertSame($variant, $response->variant);
    }

    #[Test]
    public function constructorHandlesEmptyArrays(): void
    {
        $response = new FormTypesResponse([], [], [], []);

        self::assertEmpty($response->category);
        self::assertEmpty($response->regional);
        self::assertEmpty($response->special);
        self::assertEmpty($response->variant);
    }
}

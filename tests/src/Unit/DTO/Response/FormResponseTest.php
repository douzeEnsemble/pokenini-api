<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FormResponse::class)]
final class FormResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new FormResponse(
            slug: 'alolan',
            name: 'Alolan Form',
        );

        self::assertSame('alolan', $response->slug);
        self::assertSame('Alolan Form', $response->name);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TrainerPreferenceResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerPreferenceResponse::class)]
final class TrainerPreferenceResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new TrainerPreferenceResponse(homePage: 'game');

        self::assertSame('game', $response->homePage);
    }
}

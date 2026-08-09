<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\CalculateGameBundlesAvailabilities;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CalculateGameBundlesAvailabilities::class)]
final class CalculateGameBundlesAvailabilitiesTest extends TestCase
{
    #[Test]
    public function serialize(): void
    {
        $message = new CalculateGameBundlesAvailabilities('12');

        $this->assertEquals(
            'O:46:"App\Message\CalculateGameBundlesAvailabilities":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}

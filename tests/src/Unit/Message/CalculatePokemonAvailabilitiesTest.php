<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\CalculatePokemonAvailabilities;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CalculatePokemonAvailabilities::class)]
class CalculatePokemonAvailabilitiesTest extends TestCase
{
    public function testSerialize(): void
    {
        $message = new CalculatePokemonAvailabilities('12');

        $this->assertEquals(
            'O:46:"App\Message\CalculatePokemonAvailabilities":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}

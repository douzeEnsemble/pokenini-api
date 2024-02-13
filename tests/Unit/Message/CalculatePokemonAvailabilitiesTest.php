<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\CalculatePokemonAvailabilities;
use PHPUnit\Framework\TestCase;

class CalculatePokemonAvailabilitiesTest extends TestCase
{
    public function testSerialize(): void
    {
        $message = new CalculatePokemonAvailabilities('12');

        $this->assertEquals(
            'O:42:"App\Message\CalculatePokemonAvailabilities":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}

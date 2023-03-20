<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\CalculateDexAvailabilities;
use PHPUnit\Framework\TestCase;

class CalculateDexAvailabilitiesTest extends TestCase
{
    public function testSerialize(): void
    {
        $message = new CalculateDexAvailabilities('12');

        $this->assertEquals(
            'O:38:"App\Message\CalculateDexAvailabilities":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}

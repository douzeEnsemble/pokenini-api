<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\UpdateGamesAvailabilities;
use PHPUnit\Framework\TestCase;

class UpdateGamesAvailabilitiesTest extends TestCase
{
    public function testSerialize(): void
    {
        $message = new UpdateGamesAvailabilities('12');

        $this->assertEquals(
            'O:37:"App\Message\UpdateGamesAvailabilities":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}

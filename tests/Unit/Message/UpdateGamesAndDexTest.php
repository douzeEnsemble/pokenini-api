<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\UpdateGamesAndDex;
use PHPUnit\Framework\TestCase;

class UpdateGamesAndDexTest extends TestCase
{
    public function testSerialize(): void
    {
        $message = new UpdateGamesAndDex('12');

        $this->assertEquals(
            'O:29:"App\Message\UpdateGamesAndDex":1:{s:8:"actionId";s:2:"12";}',
            serialize($message)
        );
    }
}

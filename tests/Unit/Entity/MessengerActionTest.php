<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\MessengerAction;
use PHPUnit\Framework\TestCase;

class MessengerActionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $messageAction = new MessengerAction('alpha');

        $this->assertSame('alpha', $messageAction->getType());
    }
}

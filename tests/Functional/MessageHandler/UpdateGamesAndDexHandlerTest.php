<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler;

use App\Message\UpdateGamesAndDex;
use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use App\Tests\Common\Traits\CounterTrait\CountMessengerActionTrait;
use App\Tests\Common\Traits\GetterTrait\GetMessengerActionTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class UpdateGamesAndDexHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use InteractsWithMessenger;
    use CounterTableTrait;
    use CountMessengerActionTrait;
    use GetMessengerActionTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testHandler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $this->assertEquals(9, $this->getTableCount('game_generation'));
        $this->assertEquals(17, $this->getTableCount('game_bundle'));
        $this->assertEquals(38, $this->getTableCount('game'));
        $this->assertEquals(6, $this->getTableCount('dex'));

        $this->assertEquals(12, $this->getMessengerActionCount());
        $this->assertEquals(7, $this->getMessengerActionToProcessCount());
        $this->assertEquals(5, $this->getMessengerActionDoneCount());

        $transport->send(
            new UpdateGamesAndDex(
                $this->getIdToProcess(UpdateGamesAndDex::class)
            )
        );

        $transport->queue()->assertContains(UpdateGamesAndDex::class, 1);

        $transport->process(1);

        $transport->queue()->assertEmpty();

        $this->assertEquals(9, $this->getTableCount('game_generation'));
        $this->assertEquals(18, $this->getTableCount('game_bundle'));
        $this->assertEquals(38, $this->getTableCount('game'));
        $this->assertEquals(22, $this->getTableCount('dex'));

        $this->assertEquals(12, $this->getMessengerActionCount());
        $this->assertEquals(6, $this->getMessengerActionToProcessCount());
        $this->assertEquals(6, $this->getMessengerActionDoneCount());
    }

    public function testExceptionHandler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $transport->send(new UpdateGamesAndDex('0a35b132-fa1d-4528-b866-dadac5876e1c'));

        $transport->queue()->assertContains(UpdateGamesAndDex::class, 1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Can't find MessengerAction #0a35b132-fa1d-4528-b866-dadac5876e1c");

        $transport->process(1);
    }
}

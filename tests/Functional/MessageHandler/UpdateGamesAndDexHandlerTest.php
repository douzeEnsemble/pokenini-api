<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler;

use App\Message\UpdateGamesAndDex;
use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\GetterTrait\GetActionLogTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class UpdateGamesAndDexHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use InteractsWithMessenger;
    use CounterTableTrait;
    use CountActionLogTrait;
    use GetActionLogTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testHandler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $this->assertEquals(9, $this->getTableCount('game_generation'));
        $this->assertEquals(19, $this->getTableCount('game_bundle'));
        $this->assertEquals(38, $this->getTableCount('game'));
        $this->assertEquals(8, $this->getTableCount('dex'));

        $beforeTotalCount = $this->getActionLogCount();
        $beforeToProcessCount = $this->getActionLogToProcessCount();
        $beforeDoneCount = $this->getActionLogDoneCount();

        $transport->send(
            new UpdateGamesAndDex(
                $this->getIdToProcess(UpdateGamesAndDex::class)
            )
        );

        $transport->queue()->assertContains(UpdateGamesAndDex::class, 1);

        $transport->process(1);

        $transport->queue()->assertEmpty();

        $this->assertEquals(9, $this->getTableCount('game_generation'));
        $this->assertEquals(19, $this->getTableCount('game_bundle'));
        $this->assertEquals(38, $this->getTableCount('game'));
        $this->assertEquals(24, $this->getTableCount('dex'));

        $this->assertEquals($beforeTotalCount, $this->getActionLogCount());
        $this->assertEquals($beforeToProcessCount - 1, $this->getActionLogToProcessCount());
        $this->assertEquals($beforeDoneCount + 1, $this->getActionLogDoneCount());
    }

    public function testExceptionHandler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $transport->send(new UpdateGamesAndDex('0a35b132-fa1d-4528-b866-dadac5876e1c'));

        $transport->queue()->assertContains(UpdateGamesAndDex::class, 1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Can't find ActionLog #0a35b132-fa1d-4528-b866-dadac5876e1c");

        $transport->process(1);
    }
}

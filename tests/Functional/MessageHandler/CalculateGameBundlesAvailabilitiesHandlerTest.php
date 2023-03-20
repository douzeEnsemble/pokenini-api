<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler;

use App\Message\CalculateGameBundlesAvailabilities;
use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use App\Tests\Common\Traits\CounterTrait\CountMessengerActionTrait;
use App\Tests\Common\Traits\GetterTrait\GetMessengerActionTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class CalculateGameBundlesAvailabilitiesHandlerTest extends KernelTestCase
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

        $this->assertEquals(22, $this->getTableCount('game_bundle_availability'));

        $this->assertEquals(12, $this->getMessengerActionCount());
        $this->assertEquals(7, $this->getMessengerActionToProcessCount());
        $this->assertEquals(5, $this->getMessengerActionDoneCount());

        $transport->send(
            new CalculateGameBundlesAvailabilities(
                $this->getIdToProcess(CalculateGameBundlesAvailabilities::class)
            )
        );

        $transport->queue()->assertContains(CalculateGameBundlesAvailabilities::class, 1);

        $transport->process(1);

        $transport->queue()->assertEmpty();

        $this->assertEquals(18, $this->getTableCount('game_bundle_availability'));

        $this->assertEquals(12, $this->getMessengerActionCount());
        $this->assertEquals(6, $this->getMessengerActionToProcessCount());
        $this->assertEquals(6, $this->getMessengerActionDoneCount());
    }

    public function testExceptionHandler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $transport->send(new CalculateGameBundlesAvailabilities('0a35b132-fa1d-4528-b866-dadac5876e1c'));

        $transport->queue()->assertContains(CalculateGameBundlesAvailabilities::class, 1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Can't find MessengerAction #0a35b132-fa1d-4528-b866-dadac5876e1c");

        $transport->process(1);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\CalculateGameBundlesShiniesAvailabilities;
use App\MessageHandler\CalculateGameBundlesShiniesAvailabilitiesHandler;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use App\Tests\Common\Traits\GetterTrait\GetActionLogTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;

/**
 * @internal
 */
#[CoversClass(CalculateGameBundlesShiniesAvailabilitiesHandler::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class CalculateGameBundlesShiniesAvailabilitiesHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CounterTableTrait;
    use CountActionLogTrait;
    use GetActionLogTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function handler(): void
    {
        $this->assertSame(32, $this->getTableCount('game_bundle_shiny_availability'));

        $beforeTotalCount = $this->getActionLogCount();
        $beforeToProcessCount = $this->getActionLogToProcessCount();
        $beforeDoneCount = $this->getActionLogDoneCount();

        self::getMessengerTransport('async')->send(
            new Envelope(new CalculateGameBundlesShiniesAvailabilities(
                $this->getIdToProcess(CalculateGameBundlesShiniesAvailabilities::class)
            ))
        );

        self::assertQueuedMessageCount(1, 'async', CalculateGameBundlesShiniesAvailabilities::class);

        self::consumeQueuedMessages('async', 1);

        self::assertQueuedMessageCount(0, 'async');

        $this->assertSame(9, $this->getTableCount('game_bundle_shiny_availability'));

        $this->assertSame($beforeTotalCount + 1, $this->getActionLogCount());
        $this->assertSame($beforeToProcessCount, $this->getActionLogToProcessCount());
        $this->assertSame($beforeDoneCount + 1, $this->getActionLogDoneCount());
    }

    #[Test]
    public function exceptionHandler(): void
    {
        self::getMessengerTransport('async')->send(new Envelope(new CalculateGameBundlesShiniesAvailabilities('0a35b132-fa1d-4528-b866-dadac5876e1c')));

        self::assertQueuedMessageCount(1, 'async', CalculateGameBundlesShiniesAvailabilities::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains("Can't find ActionLog #0a35b132-fa1d-4528-b866-dadac5876e1c");

        self::consumeQueuedMessages('async', 1);
    }
}

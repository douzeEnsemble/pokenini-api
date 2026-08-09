<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\UpdateGamesCollectionsAndDex;
use App\MessageHandler\UpdateGamesCollectionsAndDexHandler;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use App\Tests\Common\Traits\GetterTrait\GetActionLogTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

/**
 * @internal
 */
#[CoversClass(UpdateGamesCollectionsAndDexHandler::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class UpdateGamesCollectionsAndDexHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use InteractsWithMessenger;
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
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $this->assertEquals(9, $this->getTableCount('game_generation'));
        $this->assertEquals(19, $this->getTableCount('game_bundle'));
        $this->assertEquals(39, $this->getTableCount('game'));
        $this->assertEquals(9, $this->getTableCount('dex'));
        $this->assertEquals(8, $this->getTableCount('collection'));

        $beforeTotalCount = $this->getActionLogCount();
        $beforeToProcessCount = $this->getActionLogToProcessCount();
        $beforeDoneCount = $this->getActionLogDoneCount();

        $transport->send(
            new UpdateGamesCollectionsAndDex(
                $this->getIdToProcess(UpdateGamesCollectionsAndDex::class)
            )
        );

        $transport->queue()->assertContains(UpdateGamesCollectionsAndDex::class, 1);

        $transport->process(1);

        $transport->queue()->assertEmpty();

        $this->assertEquals(9, $this->getTableCount('game_generation'));
        $this->assertEquals(19, $this->getTableCount('game_bundle'));
        $this->assertEquals(39, $this->getTableCount('game'));
        $this->assertEquals(25, $this->getTableCount('dex'));
        $this->assertEquals(8, $this->getTableCount('collection'));

        $this->assertEquals($beforeTotalCount + 1, $this->getActionLogCount());
        $this->assertEquals($beforeToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($beforeDoneCount + 1, $this->getActionLogDoneCount());
    }

    #[Test]
    public function exceptionHandler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $transport->send(new UpdateGamesCollectionsAndDex('0a35b132-fa1d-4528-b866-dadac5876e1c'));

        $transport->queue()->assertContains(UpdateGamesCollectionsAndDex::class, 1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains("Can't find ActionLog #0a35b132-fa1d-4528-b866-dadac5876e1c");

        $transport->process(1);
    }
}

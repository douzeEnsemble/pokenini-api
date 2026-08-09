<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\UpdatePokemons;
use App\MessageHandler\UpdatePokemonsHandler;
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
#[CoversClass(UpdatePokemonsHandler::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class UpdatePokemonsHandlerTest extends KernelTestCase
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

        $this->assertEquals(26, $this->getTableCount('pokemon'));

        $beforeTotalCount = $this->getActionLogCount();
        $beforeToProcessCount = $this->getActionLogToProcessCount();
        $beforeDoneCount = $this->getActionLogDoneCount();

        $transport->send(
            new UpdatePokemons(
                $this->getIdToProcess(UpdatePokemons::class)
            )
        );

        $transport->queue()->assertContains(UpdatePokemons::class, 1);

        $transport->process(1);

        $transport->queue()->assertEmpty();

        $this->assertEquals(1818, $this->getTableCount('pokemon'));

        $this->assertEquals($beforeTotalCount + 1, $this->getActionLogCount());
        $this->assertEquals($beforeToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($beforeDoneCount + 1, $this->getActionLogDoneCount());
    }

    #[Test]
    public function exceptionHandler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $transport->send(new UpdatePokemons('0a35b132-fa1d-4528-b866-dadac5876e1c'));

        $transport->queue()->assertContains(UpdatePokemons::class, 1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains("Can't find ActionLog #0a35b132-fa1d-4528-b866-dadac5876e1c");

        $transport->process(1);
    }
}

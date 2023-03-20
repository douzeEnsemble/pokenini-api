<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler;

use App\Message\UpdatePokemons;
use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use App\Tests\Common\Traits\CounterTrait\CountMessengerActionTrait;
use App\Tests\Common\Traits\GetterTrait\GetMessengerActionTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class UpdatePokemonsHandlerTest extends KernelTestCase
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

        $this->assertEquals(19, $this->getTableCount('pokemon'));

        $this->assertEquals(12, $this->getMessengerActionCount());
        $this->assertEquals(7, $this->getMessengerActionToProcessCount());
        $this->assertEquals(5, $this->getMessengerActionDoneCount());

        $transport->send(
            new UpdatePokemons(
                $this->getIdToProcess(UpdatePokemons::class)
            )
        );

        $transport->queue()->assertContains(UpdatePokemons::class, 1);

        $transport->process(1);

        $transport->queue()->assertEmpty();

        $this->assertEquals(1816, $this->getTableCount('pokemon'));

        $this->assertEquals(12, $this->getMessengerActionCount());
        $this->assertEquals(6, $this->getMessengerActionToProcessCount());
        $this->assertEquals(6, $this->getMessengerActionDoneCount());
    }

    public function testExceptionHandler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $transport->send(new UpdatePokemons('0a35b132-fa1d-4528-b866-dadac5876e1c'));

        $transport->queue()->assertContains(UpdatePokemons::class, 1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Can't find MessengerAction #0a35b132-fa1d-4528-b866-dadac5876e1c");

        $transport->process(1);
    }
}

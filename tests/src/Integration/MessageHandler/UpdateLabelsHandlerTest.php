<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\UpdateLabels;
use App\MessageHandler\UpdateLabelsHandler;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use App\Tests\Common\Traits\GetterTrait\GetActionLogTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

/**
 * @internal
 */
#[CoversClass(UpdateLabelsHandler::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class UpdateLabelsHandlerTest extends KernelTestCase
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

    public function testHandler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $this->assertEquals(5, $this->getTableCount('catch_state'));
        $this->assertEquals(10, $this->getTableCount('region'));
        $this->assertEquals(3, $this->getTableCount('category_form'));
        $this->assertEquals(3, $this->getTableCount('regional_form'));
        $this->assertEquals(4, $this->getTableCount('special_form'));
        $this->assertEquals(7, $this->getTableCount('variant_form'));
        $this->assertEquals(19, $this->getTableCount('type'));

        $beforeTotalCount = $this->getActionLogCount();
        $beforeToProcessCount = $this->getActionLogToProcessCount();
        $beforeDoneCount = $this->getActionLogDoneCount();

        $transport->send(
            new UpdateLabels(
                $this->getIdToProcess(UpdateLabels::class)
            )
        );

        $transport->queue()->assertContains(UpdateLabels::class, 1);

        $transport->process(1);

        $transport->queue()->assertEmpty();

        $this->assertEquals(9, $this->getTableCount('catch_state'));
        $this->assertEquals(10, $this->getTableCount('region'));
        $this->assertEquals(4, $this->getTableCount('category_form'));
        $this->assertEquals(4, $this->getTableCount('regional_form'));
        $this->assertEquals(5, $this->getTableCount('special_form'));
        $this->assertEquals(8, $this->getTableCount('variant_form'));
        $this->assertEquals(20, $this->getTableCount('type'));

        $this->assertEquals($beforeTotalCount + 1, $this->getActionLogCount());
        $this->assertEquals($beforeToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($beforeDoneCount + 1, $this->getActionLogDoneCount());
    }

    public function testExceptionHandler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $transport->send(new UpdateLabels('0a35b132-fa1d-4528-b866-dadac5876e1c'));

        $transport->queue()->assertContains(UpdateLabels::class, 1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Can't find ActionLog #0a35b132-fa1d-4528-b866-dadac5876e1c");

        $transport->process(1);
    }
}

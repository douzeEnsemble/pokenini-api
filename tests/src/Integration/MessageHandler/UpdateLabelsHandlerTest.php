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
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function handler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $this->assertSame(5, $this->getTableCount('catch_state'));
        $this->assertSame(3, $this->getTableCount('dex_category'));
        $this->assertSame(10, $this->getTableCount('region'));
        $this->assertSame(3, $this->getTableCount('category_form'));
        $this->assertSame(3, $this->getTableCount('regional_form'));
        $this->assertSame(4, $this->getTableCount('special_form'));
        $this->assertSame(7, $this->getTableCount('variant_form'));
        $this->assertSame(19, $this->getTableCount('type'));

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

        $this->assertSame(9, $this->getTableCount('catch_state'));
        $this->assertSame(4, $this->getTableCount('dex_category'));
        $this->assertSame(10, $this->getTableCount('region'));
        $this->assertSame(4, $this->getTableCount('category_form'));
        $this->assertSame(4, $this->getTableCount('regional_form'));
        $this->assertSame(5, $this->getTableCount('special_form'));
        $this->assertSame(8, $this->getTableCount('variant_form'));
        $this->assertSame(20, $this->getTableCount('type'));

        $this->assertSame($beforeTotalCount + 1, $this->getActionLogCount());
        $this->assertSame($beforeToProcessCount, $this->getActionLogToProcessCount());
        $this->assertSame($beforeDoneCount + 1, $this->getActionLogDoneCount());
    }

    #[Test]
    public function exceptionHandler(): void
    {
        $transport = $this->transport('async');
        $transport->throwExceptions();

        $transport->send(new UpdateLabels('0a35b132-fa1d-4528-b866-dadac5876e1c'));

        $transport->queue()->assertContains(UpdateLabels::class, 1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains("Can't find ActionLog #0a35b132-fa1d-4528-b866-dadac5876e1c");

        $transport->process(1);
    }
}

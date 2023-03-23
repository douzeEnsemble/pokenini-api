<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;

class UpdateLabelsCommandTest extends AbstractUpdaterCommandTest
{
    use CounterTableTrait;
    use CountActionLogTrait;

    public function testUpdate(): void
    {
        $this->assertEquals(5, $this->getTableCount('catch_state'));
        $this->assertEquals(3, $this->getTableCount('category_form'));
        $this->assertEquals(3, $this->getTableCount('regional_form'));
        $this->assertEquals(3, $this->getTableCount('special_form'));
        $this->assertEquals(7, $this->getTableCount('variant_form'));
        $this->assertEquals(10, $this->getTableCount('region'));

        $this->assertEquals(7, $this->getActionLogToProcessCount());
        $this->assertEquals(5, $this->getActionLogDoneCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(9, $this->getTableCount('catch_state'));
        $this->assertEquals(4, $this->getTableCount('category_form'));
        $this->assertEquals(4, $this->getTableCount('regional_form'));
        $this->assertEquals(5, $this->getTableCount('special_form'));
        $this->assertEquals(8, $this->getTableCount('variant_form'));
        $this->assertEquals(8, $this->getTableCount('variant_form'));
        $this->assertEquals(10, $this->getTableCount('region'));

        $this->assertEquals(7, $this->getActionLogToProcessCount());
        $this->assertEquals(6, $this->getActionLogDoneCount());

        $this->assertStringContainsString("6 catch's states updated", $commandTester->getDisplay());
        $this->assertStringContainsString("0 regions updated", $commandTester->getDisplay());
        $this->assertStringContainsString("4 category forms updated", $commandTester->getDisplay());
        $this->assertStringContainsString("4 regional forms updated", $commandTester->getDisplay());
        $this->assertStringContainsString("5 special forms updated", $commandTester->getDisplay());
        $this->assertStringContainsString("8 variant forms updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:labels';
    }
}

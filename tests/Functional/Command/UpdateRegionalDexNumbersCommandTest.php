<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CountRegionalDexNumberTrait;

class UpdateRegionalDexNumbersCommandTest extends AbstractUpdaterCommandTest
{
    use CountRegionalDexNumberTrait;
    use CountActionLogTrait;

    public function testUpdate(): void
    {
        $this->assertGreaterThan(0, $this->getRegionalDexNumberCount());

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(2863, $this->getRegionalDexNumberCount());

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertStringContainsString("2863 regional dex numbers updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:regional_dex_numbers';
    }
}

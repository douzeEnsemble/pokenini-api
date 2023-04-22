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

        $this->assertEquals(9, $this->getActionLogToProcessCount());
        $this->assertEquals(5, $this->getActionLogDoneCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(2863, $this->getRegionalDexNumberCount());

        $this->assertEquals(9, $this->getActionLogToProcessCount());
        $this->assertEquals(6, $this->getActionLogDoneCount());

        $this->assertStringContainsString("2863 regional dex numbers updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:regional_dex_numbers';
    }
}

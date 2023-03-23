<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CountMessengerActionTrait;
use App\Tests\Common\Traits\CounterTrait\CountRegionalDexNumberTrait;

class UpdateRegionalDexNumbersCommandTest extends AbstractUpdaterCommandTest
{
    use CountRegionalDexNumberTrait;
    use CountMessengerActionTrait;

    public function testUpdate(): void
    {
        $this->assertGreaterThan(0, $this->getRegionalDexNumberCount());

        $this->assertEquals(7, $this->getMessengerActionToProcessCount());
        $this->assertEquals(5, $this->getMessengerActionDoneCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(2863, $this->getRegionalDexNumberCount());

        $this->assertEquals(7, $this->getMessengerActionToProcessCount());
        $this->assertEquals(6, $this->getMessengerActionDoneCount());

        $this->assertStringContainsString("2863 regional dex numbers updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:regional_dex_numbers';
    }
}

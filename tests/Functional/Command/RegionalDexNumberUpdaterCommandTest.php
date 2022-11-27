<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CountRegionalDexNumberTrait;

class RegionalDexNumberUpdaterCommandTest extends AbstractUpdaterCommandTest
{
    use CountRegionalDexNumberTrait;

    public function testUpdate(): void
    {
        $this->assertGreaterThan(0, $this->getRegionalDexNumberCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(2863, $this->getRegionalDexNumberCount());

        $this->assertStringContainsString("2863 regional dex numbers updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:regional_dex_number';
    }
}

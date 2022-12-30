<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;

class LabelsUpdaterCommandTest extends AbstractUpdaterCommandTest
{
    use CounterTableTrait;

    public function testUpdate(): void
    {
        $this->assertEquals(5, $this->getTableCount('catch_state'));
        $this->assertEquals(3, $this->getTableCount('category_form'));
        $this->assertEquals(3, $this->getTableCount('regional_form'));
        $this->assertEquals(3, $this->getTableCount('special_form'));
        $this->assertEquals(7, $this->getTableCount('variant_form'));
        $this->assertEquals(10, $this->getTableCount('region'));

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(9, $this->getTableCount('catch_state'));
        $this->assertEquals(4, $this->getTableCount('category_form'));
        $this->assertEquals(4, $this->getTableCount('regional_form'));
        $this->assertEquals(5, $this->getTableCount('special_form'));
        $this->assertEquals(8, $this->getTableCount('variant_form'));
        $this->assertEquals(8, $this->getTableCount('variant_form'));
        $this->assertEquals(10, $this->getTableCount('region'));

        $this->assertStringContainsString("Labels updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:labels';
    }
}

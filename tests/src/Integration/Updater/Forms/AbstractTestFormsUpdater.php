<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater\Forms;

use App\Exception\InvalidSheetDataException;
use App\Tests\Integration\Updater\AbstractTestUpdater;
use PHPUnit\Framework\Attributes\Test;

abstract class AbstractTestFormsUpdater extends AbstractTestUpdater
{
    #[Test]
    public function doEmptyData(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessageIsOrContains('There is not data in spreadsheet');

        $service->execute('form / empty_data');
    }

    #[Test]
    public function doZeroData(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessageIsOrContains("Can't get data for range 'form / zero_data'!A2:D");

        $service->execute('form / zero_data');
    }

    #[Test]
    public function doAnotherList(): void
    {
        $this->assertEquals($this->initialTotalCount, $this->getTableCount());
        $this->assertEquals(0, $this->getTableDeletedAtCount());

        $service = $this->getService();

        $service->execute('form / another_list');

        $this->assertEquals($this->initialTotalCount + 4, $this->getTableCount());
        $this->assertEquals($this->initialTotalCount, $this->getTableDeletedAtCount());
    }

    #[Test]
    public function doDifferentColumnsOrder(): void
    {
        $this->assertEquals($this->initialTotalCount, $this->getTableCount());
        $this->assertEquals(0, $this->getTableDeletedAtCount());

        $service = $this->getService();

        $service->execute('form / different_columns_order');

        $this->assertEquals($this->initialTotalCount + 4, $this->getTableCount());
        $this->assertEquals($this->initialTotalCount, $this->getTableDeletedAtCount());
    }
}

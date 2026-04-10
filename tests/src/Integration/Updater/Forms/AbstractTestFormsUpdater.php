<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater\Forms;

use App\Exception\InvalidSheetDataException;
use App\Tests\Integration\Updater\AbstractTestUpdater;

abstract class AbstractTestFormsUpdater extends AbstractTestUpdater
{
    public function testDoEmptyData(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage('There is not data in spreadsheet');

        $service->execute('form / empty_data');
    }

    public function testDoZeroData(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage("Can't get data for range 'form / zero_data'!A2:D");

        $service->execute('form / zero_data');
    }

    public function testDoAnotherList(): void
    {
        $this->assertEquals($this->initialTotalCount, $this->getTableCount());
        $this->assertEquals(0, $this->getTableDeletedAtCount());

        $service = $this->getService();

        $service->execute('form / another_list');

        $this->assertEquals($this->initialTotalCount + 4, $this->getTableCount());
        $this->assertEquals($this->initialTotalCount, $this->getTableDeletedAtCount());
    }

    public function testDoDifferentColumnsOrder(): void
    {
        $this->assertEquals($this->initialTotalCount, $this->getTableCount());
        $this->assertEquals(0, $this->getTableDeletedAtCount());

        $service = $this->getService();

        $service->execute('form / different_columns_order');

        $this->assertEquals($this->initialTotalCount + 4, $this->getTableCount());
        $this->assertEquals($this->initialTotalCount, $this->getTableDeletedAtCount());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater\Forms;

use App\Exception\InvalidSheetDataException;
use App\Tests\Integration\Updater\AbstractTestNamedUpdater;
use PHPUnit\Framework\Attributes\Test;

abstract class AbstractTestFormsUpdater extends AbstractTestNamedUpdater
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
        $this->assertSame($this->initialTotalCount, $this->getTableCount());
        $this->assertSame(0, $this->getTableDeletedAtCount());

        $service = $this->getService();

        $service->execute('form / another_list');

        $this->assertSame($this->initialTotalCount + 4, $this->getTableCount());
        $this->assertSame($this->initialTotalCount, $this->getTableDeletedAtCount());
    }

    #[Test]
    public function doDifferentColumnsOrder(): void
    {
        $this->assertSame($this->initialTotalCount, $this->getTableCount());
        $this->assertSame(0, $this->getTableDeletedAtCount());

        $service = $this->getService();

        $service->execute('form / different_columns_order');

        $this->assertSame($this->initialTotalCount + 4, $this->getTableCount());
        $this->assertSame($this->initialTotalCount, $this->getTableDeletedAtCount());
    }
}

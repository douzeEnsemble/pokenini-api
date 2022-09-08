<?php

namespace functionnal\Service\Updater\Form;

use App\Exception\InvalidSheetDataException;
use functionnal\Service\Updater\AbstractUpdaterTest;

abstract class AbstractFormUpdaterTest extends AbstractUpdaterTest
{
    public function testDoEmptyData(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage('There is not data in spreadsheet');

        $service->do('form / empty_data');
    }

    public function testDoZeroData(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage("Can't get data for range 'form / zero_data'!A2:C");

        $service->do('form / zero_data');
    }

    public function testDoAnotherList(): void
    {
        $this->assertEquals($this->initialTotalCount, $this->getTableCount());

        $service = $this->getService();

        $service->do('form / another_list');

        $this->assertEquals($this->initialTotalCount+4, $this->getTableCount());
    }

    public function testDoDifferentColumnsOrder(): void
    {
        $this->assertEquals($this->initialTotalCount, $this->getTableCount());

        $service = $this->getService();

        $service->do('form / different_columns_order');

        $this->assertEquals($this->initialTotalCount+4, $this->getTableCount());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\RegionalDexNumbersUpdater;

class RegionalDexNumbersUpdaterTest extends AbstractTestUpdater
{
    protected int $initialTotalCount = 12;
    protected int $finalTotalCount = 2863;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Regional Dex Number';
    protected string $tableName = 'regional_dex_number';

    protected function getService(): AbstractUpdater
    {
        /** @var RegionalDexNumbersUpdater */
        return static::getContainer()->get(RegionalDexNumbersUpdater::class);
    }

    /**
     * There is no "deleted_at" field in the table
     */
    protected function getTableDeletedAtCount(): int
    {
        return 0;
    }
}

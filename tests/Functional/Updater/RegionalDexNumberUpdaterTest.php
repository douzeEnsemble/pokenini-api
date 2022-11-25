<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\RegionalDexNumberUpdater;

class RegionalDexNumberUpdaterTest extends AbstractUpdaterTest
{
    protected int $initialTotalCount = 12;
    protected int $finalTotalCount = 2863;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Regional Dex Number';
    protected string $tableName = 'regional_dex_number';

    protected function getService(): AbstractUpdater
    {
        /** @var RegionalDexNumberUpdater */
        return static::getContainer()->get(RegionalDexNumberUpdater::class);
    }

    /**
     * There is no "deleted_at" field in the table
     */
    protected function getTableDeletedAtCount(): int
    {
        return 0;
    }
}

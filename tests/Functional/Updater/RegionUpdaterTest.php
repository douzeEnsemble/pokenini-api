<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\RegionUpdater;

class RegionUpdaterTest extends AbstractUpdaterTest
{
    protected int $initialTotalCount = 8;
    protected int $finalTotalCount = 11;
    protected int $mustBeDeletedTotalCount = 1;
    protected string $sheetName = 'Region';
    protected string $tableName = 'region';

    protected function getService(): AbstractUpdater
    {
        /** @var RegionUpdater */
        return static::getContainer()->get(RegionUpdater::class);
    }
}

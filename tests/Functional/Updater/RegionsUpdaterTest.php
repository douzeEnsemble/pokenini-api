<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\RegionsUpdater;

class RegionsUpdaterTest extends AbstractTestUpdater
{
    protected int $initialTotalCount = 10;
    protected int $finalTotalCount = 10;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Region';
    protected string $tableName = 'region';

    protected function getService(): AbstractUpdater
    {
        /** @var RegionsUpdater */
        return static::getContainer()->get(RegionsUpdater::class);
    }
}

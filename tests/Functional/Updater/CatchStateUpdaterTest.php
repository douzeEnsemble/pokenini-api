<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\CatchStateUpdater;

class CatchStateUpdaterTest extends AbstractUpdaterTest
{
    protected int $initialTotalCount = 5;
    protected int $finalTotalCount = 9;
    protected int $initialDeletedTotalCount = 1;
    protected int $mustBeDeletedTotalCount = 3;
    protected string $sheetName = 'Catch state';
    protected string $tableName = 'catch_state';

    protected function getService(): AbstractUpdater
    {
        /** @var CatchStateUpdater */
        return static::getContainer()->get(CatchStateUpdater::class);
    }
}

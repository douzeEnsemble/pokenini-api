<?php

declare(strict_types=1);

namespace App\Tests\Functionnal\Service\Updater;

use App\Service\Updater\AbstractUpdater;
use App\Service\Updater\CatchStateUpdater;

class CatchStateUpdaterTest extends AbstractUpdaterTest
{
    protected int $initialTotalCount = 4;
    protected int $finalTotalCount = 7;
    protected int $mustBeDeletedTotalCount = 2;
    protected string $sheetName = 'Catch state';
    protected string $tableName = 'catch_state';

    protected function getService(): AbstractUpdater
    {
        /** @var CatchStateUpdater */
        return static::getContainer()->get(CatchStateUpdater::class);
    }
}

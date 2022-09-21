<?php

declare(strict_types=1);

namespace App\Tests\Functionnal\Service\Updater;

use App\Service\Updater\AbstractUpdater;
use App\Service\Updater\DexUpdater;

class DexUpdaterTest extends AbstractUpdaterTest
{
    protected int $initialTotalCount = 6;
    protected int $finalTotalCount = 22;
    protected int $mustBeDeletedTotalCount = 1;
    protected string $sheetName = 'Dex';
    protected string $tableName = 'dex';

    protected function getService(): AbstractUpdater
    {
        /** @var DexUpdater */
        return static::getContainer()->get(DexUpdater::class);
    }
}

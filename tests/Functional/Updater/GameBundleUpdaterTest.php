<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\GameBundleUpdater;

class GameBundleUpdaterTest extends AbstractUpdaterTest
{
    protected int $initialTotalCount = 16;
    protected int $finalTotalCount = 17;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Game Bundle';
    protected string $tableName = 'game_bundle';

    protected function getService(): AbstractUpdater
    {
        /** @var GameBundleUpdater */
        return static::getContainer()->get(GameBundleUpdater::class);
    }
}

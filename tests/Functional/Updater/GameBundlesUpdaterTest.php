<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\GameBundlesUpdater;

class GameBundlesUpdaterTest extends AbstractUpdaterTest
{
    protected int $initialTotalCount = 17;
    protected int $finalTotalCount = 18;
    protected int $mustBeDeletedTotalCount = 1;
    protected string $sheetName = 'Game Bundle';
    protected string $tableName = 'game_bundle';

    protected function getService(): AbstractUpdater
    {
        /** @var GameBundlesUpdater */
        return static::getContainer()->get(GameBundlesUpdater::class);
    }
}

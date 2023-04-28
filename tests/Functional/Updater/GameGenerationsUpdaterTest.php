<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\GameGenerationsUpdater;

class GameGenerationsUpdaterTest extends AbstractTestUpdater
{
    protected int $initialTotalCount = 9;
    protected int $finalTotalCount = 9;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Game Generation';
    protected string $tableName = 'game_generation';

    protected function getService(): AbstractUpdater
    {
        /** @var GameGenerationsUpdater */
        return static::getContainer()->get(GameGenerationsUpdater::class);
    }
}

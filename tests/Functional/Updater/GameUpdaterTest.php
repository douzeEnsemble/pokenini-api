<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\GameUpdater;

class GameUpdaterTest extends AbstractUpdaterTest
{
    protected int $initialTotalCount = 38;
    protected int $finalTotalCount = 40;
    protected int $mustBeDeletedTotalCount = 3;
    protected string $sheetName = 'Game';
    protected string $tableName = 'game';

    protected function getService(): AbstractUpdater
    {
        /** @var GameUpdater */
        return static::getContainer()->get(GameUpdater::class);
    }
}

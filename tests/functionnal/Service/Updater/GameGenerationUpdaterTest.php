<?php

declare(strict_types=1);

namespace App\Tests\Functionnal\Service\Updater;

use App\Service\Updater\AbstractUpdater;
use App\Service\Updater\GameGenerationUpdater;

class GameGenerationUpdaterTest extends AbstractUpdaterTest
{
    protected int $initialTotalCount = 8;
    protected int $finalTotalCount = 9;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Game Generation';
    protected string $tableName = 'game_generation';

    protected function getService(): AbstractUpdater
    {
        /** @var GameGenerationUpdater */
        return static::getContainer()->get(GameGenerationUpdater::class);
    }
}

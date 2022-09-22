<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\GameGenerationUpdater;

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

<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Tests\Common\Traits\CounterTrait\CountGameAvailabilityTrait;
use App\Updater\AbstractUpdater;
use App\Updater\GameAvailabilityUpdater;

class GameAvailabilityUpdaterTest extends AbstractUpdaterTest
{
    use CountGameAvailabilityTrait;

    protected int $initialTotalCount = 23;
    protected int $finalTotalCount = 7560;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Games Availability';
    protected string $tableName = 'game_availability';

    protected function getService(): AbstractUpdater
    {
        /** @var GameAvailabilityUpdater */
        return static::getContainer()->get(GameAvailabilityUpdater::class);
    }

    /**
     * There is no "deleted_at" field in the table
     */
    protected function getTableDeletedAtCount(): int
    {
        return 0;
    }
}

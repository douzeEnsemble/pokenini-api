<?php

namespace App\Tests\Functionnal\Service\Updater;

use App\Service\Updater\AbstractUpdater;
use App\Service\Updater\GameAvailabilityUpdater;
use App\Tests\Resources\Traits\CounterTrait\CountGameAvailabilityTrait;

class GameAvailabilityUpdaterTest extends AbstractUpdaterTest
{
    use CountGameAvailabilityTrait;

    protected int $initialTotalCount = 23;
    protected int $finalTotalCount = 34596;
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

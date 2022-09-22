<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\GameAvailabilityUpdater;
use App\Tests\Resources\Traits\CounterTrait\CountGameAvailabilityTrait;

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

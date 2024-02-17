<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Tests\Common\Traits\CounterTrait\CountGameAvailabilityTrait;
use App\Updater\AbstractUpdater;
use App\Updater\GamesAvailabilitiesUpdater;

class GamesAvailabilitiesUpdaterTest extends AbstractTestUpdater
{
    use CountGameAvailabilityTrait;

    protected int $initialTotalCount = 28;
    protected int $finalTotalCount = 68970;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Games Availability';
    protected string $tableName = 'game_availability';

    protected function getService(): AbstractUpdater
    {
        /** @var GamesAvailabilitiesUpdater */
        return static::getContainer()->get(GamesAvailabilitiesUpdater::class);
    }

    /**
     * There is no "deleted_at" field in the table
     */
    protected function getTableDeletedAtCount(): int
    {
        return 0;
    }
}

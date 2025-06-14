<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Tests\Common\Traits\CounterTrait\CountCollectionAvailabilityTrait;
use App\Updater\AbstractUpdater;
use App\Updater\CollectionsAvailabilitiesUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(CollectionsAvailabilitiesUpdater::class)]
#[CoversClass(AbstractUpdater::class)]
class CollectionsAvailabilitiesUpdaterTest extends AbstractTestUpdater
{
    use CountCollectionAvailabilityTrait;

    protected int $initialTotalCount = 6;
    protected int $finalTotalCount = 968;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Collections Availability';
    protected string $tableName = 'collection_availability';

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var CollectionsAvailabilitiesUpdater */
        return static::getContainer()->get(CollectionsAvailabilitiesUpdater::class);
    }

    /**
     * There is no "deleted_at" field in the table.
     */
    #[\Override]
    protected function getTableDeletedAtCount(): int
    {
        return 0;
    }
}

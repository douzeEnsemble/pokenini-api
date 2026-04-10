<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\CollectionsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(CollectionsUpdater::class)]
#[CoversClass(AbstractUpdater::class)]
class CollectionsUpdaterTest extends AbstractTestUpdater
{
    protected int $initialTotalCount = 8;
    protected int $finalTotalCount = 8;
    protected int $initialDeletedTotalCount = 0;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Collection';
    protected string $tableName = 'collection';

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var CollectionsUpdater */
        return static::getContainer()->get(CollectionsUpdater::class);
    }
}

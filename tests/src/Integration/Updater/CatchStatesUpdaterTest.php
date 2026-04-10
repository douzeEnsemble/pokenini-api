<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\CatchStatesUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(CatchStatesUpdater::class)]
#[CoversClass(AbstractUpdater::class)]
final class CatchStatesUpdaterTest extends AbstractTestUpdater
{
    protected int $initialTotalCount = 5;
    protected int $finalTotalCount = 9;
    protected int $initialDeletedTotalCount = 1;
    protected int $mustBeDeletedTotalCount = 3;
    protected string $sheetName = 'Catch state';
    protected string $tableName = 'catch_state';

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var CatchStatesUpdater */
        return static::getContainer()->get(CatchStatesUpdater::class);
    }
}

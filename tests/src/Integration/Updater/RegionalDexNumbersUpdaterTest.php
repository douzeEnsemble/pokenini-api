<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\RegionalDexNumbersUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(RegionalDexNumbersUpdater::class)]
#[CoversClass(AbstractUpdater::class)]
final class RegionalDexNumbersUpdaterTest extends AbstractTestUpdater
{
    protected int $initialTotalCount = 12;
    protected int $finalTotalCount = 4419;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Regional Dex Number';
    protected string $tableName = 'regional_dex_number';

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var RegionalDexNumbersUpdater */
        return static::getContainer()->get(RegionalDexNumbersUpdater::class);
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

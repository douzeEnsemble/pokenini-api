<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\DexCategoriesUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DexCategoriesUpdater::class)]
#[CoversClass(AbstractUpdater::class)]
final class DexCategoriesUpdaterTest extends AbstractTestUpdater
{
    protected int $initialTotalCount = 3;
    protected int $finalTotalCount = 4;
    protected int $mustBeDeletedTotalCount = 1;
    protected string $sheetName = 'Dex Category';
    protected string $tableName = 'dex_category';

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var DexCategoriesUpdater */
        return self::getContainer()->get(DexCategoriesUpdater::class);
    }
}

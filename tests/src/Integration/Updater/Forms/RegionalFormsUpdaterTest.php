<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater\Forms;

use App\Updater\AbstractUpdater;
use App\Updater\Forms\RegionalFormsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(RegionalFormsUpdater::class)]
class RegionalFormsUpdaterTest extends AbstractTestFormsUpdater
{
    protected int $initialTotalCount = 3;
    protected int $finalTotalCount = 4;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'form / Regional form';
    protected string $tableName = 'regional_form';

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var RegionalFormsUpdater */
        return static::getContainer()->get(RegionalFormsUpdater::class);
    }
}

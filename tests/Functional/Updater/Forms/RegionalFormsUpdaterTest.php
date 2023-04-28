<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater\Forms;

use App\Updater\AbstractUpdater;
use App\Updater\Forms\RegionalFormsUpdater;

class RegionalFormsUpdaterTest extends AbstractTestFormsUpdater
{
    protected int $initialTotalCount = 3;
    protected int $finalTotalCount = 4;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'form / Regional form';
    protected string $tableName = 'regional_form';

    protected function getService(): AbstractUpdater
    {
        /** @var RegionalFormsUpdater */
        return static::getContainer()->get(RegionalFormsUpdater::class);
    }
}

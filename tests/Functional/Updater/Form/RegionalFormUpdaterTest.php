<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater\Form;

use App\Updater\AbstractUpdater;
use App\Updater\Form\RegionalFormUpdater;

class RegionalFormUpdaterTest extends AbstractFormUpdaterTest
{
    protected int $initialTotalCount = 3;
    protected int $finalTotalCount = 4;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'form / Regional form';
    protected string $tableName = 'regional_form';

    protected function getService(): AbstractUpdater
    {
        /** @var RegionalFormUpdater */
        return static::getContainer()->get(RegionalFormUpdater::class);
    }
}

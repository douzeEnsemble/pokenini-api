<?php

namespace functionnal\Service\Updater\Form;

use App\Service\Updater\AbstractUpdater;
use App\Service\Updater\Form\RegionalFormUpdater;

class RegionalFormUpdaterTest extends AbstractFormUpdaterTest
{
    protected int $initialTotalCount = 3;
    protected int $finalTotalCount = 4;
    protected string $sheetName = 'form / Regional form';
    protected string $tableName = 'regional_form';

    protected function getService(): AbstractUpdater
    {
        /** @var RegionalFormUpdater */
        return static::getContainer()->get(RegionalFormUpdater::class);
    }
}

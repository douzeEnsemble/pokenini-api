<?php

namespace App\Tests\Functionnal\Service\Updater\Form;

use App\Service\Updater\AbstractUpdater;
use App\Service\Updater\Form\SpecialFormUpdater;

class SpecialFormUpdaterTest extends AbstractFormUpdaterTest
{
    protected int $initialTotalCount = 3;
    protected int $finalTotalCount = 5;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'form / Special form';
    protected string $tableName = 'special_form';

    protected function getService(): AbstractUpdater
    {
        /** @var SpecialFormUpdater */
        return static::getContainer()->get(SpecialFormUpdater::class);
    }
}

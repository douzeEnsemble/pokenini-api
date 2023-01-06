<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater\Forms;

use App\Updater\AbstractUpdater;
use App\Updater\Forms\SpecialFormsUpdater;

class SpecialFormsUpdaterTest extends AbstractFormsUpdaterTest
{
    protected int $initialTotalCount = 3;
    protected int $finalTotalCount = 5;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'form / Special form';
    protected string $tableName = 'special_form';

    protected function getService(): AbstractUpdater
    {
        /** @var SpecialFormsUpdater */
        return static::getContainer()->get(SpecialFormsUpdater::class);
    }
}

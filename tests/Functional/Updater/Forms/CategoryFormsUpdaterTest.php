<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater\Forms;

use App\Updater\AbstractUpdater;
use App\Updater\Forms\CategoryFormsUpdater;

class CategoryFormsUpdaterTest extends AbstractTestFormsUpdater
{
    protected int $initialTotalCount = 3;
    protected int $finalTotalCount = 4;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'form / Category form';
    protected string $tableName = 'category_form';

    protected function getService(): AbstractUpdater
    {
        /** @var CategoryFormsUpdater */
        return static::getContainer()->get(CategoryFormsUpdater::class);
    }
}

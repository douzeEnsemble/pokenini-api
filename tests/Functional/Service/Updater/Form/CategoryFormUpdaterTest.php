<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Updater\Form;

use App\Updater\AbstractUpdater;
use App\Updater\Form\CategoryFormUpdater;

class CategoryFormUpdaterTest extends AbstractFormUpdaterTest
{
    protected int $initialTotalCount = 3;
    protected int $finalTotalCount = 4;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'form / Category form';
    protected string $tableName = 'category_form';

    protected function getService(): AbstractUpdater
    {
        /** @var CategoryFormUpdater */
        return static::getContainer()->get(CategoryFormUpdater::class);
    }
}

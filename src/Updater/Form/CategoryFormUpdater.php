<?php

declare(strict_types=1);

namespace App\Updater\Form;

class CategoryFormUpdater extends AbstractFormUpdater
{
    protected string $sheetName = 'Category form';
    protected string $tableName = 'category_form';
    protected string $statisticName = 'category_form';
}

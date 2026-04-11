<?php

declare(strict_types=1);

namespace App\Updater\Forms;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CategoryFormsUpdater extends AbstractFormsUpdater
{
    protected string $sheetName = 'Category form';
    protected string $tableName = 'category_form';
    protected string $statisticName = 'category_forms';
}

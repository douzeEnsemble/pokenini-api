<?php

declare(strict_types=1);

namespace App\Updater\Forms;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CategoryFormsUpdater extends AbstractFormsUpdater
{
    #[\Override]
    protected string $sheetName = 'Category form';
    #[\Override]
    protected string $tableName = 'category_form';
    #[\Override]
    protected string $statisticName = 'category_forms';
}

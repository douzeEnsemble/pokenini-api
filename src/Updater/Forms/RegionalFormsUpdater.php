<?php

declare(strict_types=1);

namespace App\Updater\Forms;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RegionalFormsUpdater extends AbstractFormsUpdater
{
    #[\Override]
    protected string $sheetName = 'Regional form';
    #[\Override]
    protected string $tableName = 'regional_form';
    #[\Override]
    protected string $statisticName = 'regional_forms';
}

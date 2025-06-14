<?php

declare(strict_types=1);

namespace App\Updater\Forms;

class RegionalFormsUpdater extends AbstractFormsUpdater
{
    protected string $sheetName = 'Regional form';
    protected string $tableName = 'regional_form';
    protected string $statisticName = 'regional_forms';
}

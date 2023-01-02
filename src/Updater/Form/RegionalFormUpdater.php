<?php

declare(strict_types=1);

namespace App\Updater\Form;

class RegionalFormUpdater extends AbstractFormUpdater
{
    protected string $sheetName = 'Regional form';
    protected string $tableName = 'regional_form';
    protected string $statisticName = 'regional_form';
}

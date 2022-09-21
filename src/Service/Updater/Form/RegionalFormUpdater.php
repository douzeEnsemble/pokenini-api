<?php

declare(strict_types=1);

namespace App\Service\Updater\Form;

class RegionalFormUpdater extends AbstractFormUpdater
{
    protected string $sheetName = 'Regional form';
    protected string $tableName = 'regional_form';
}

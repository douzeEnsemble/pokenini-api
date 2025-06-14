<?php

declare(strict_types=1);

namespace App\Updater\Forms;

class SpecialFormsUpdater extends AbstractFormsUpdater
{
    protected string $sheetName = 'Special form';
    protected string $tableName = 'special_form';
    protected string $statisticName = 'special_forms';
}

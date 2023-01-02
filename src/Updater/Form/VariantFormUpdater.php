<?php

declare(strict_types=1);

namespace App\Updater\Form;

class VariantFormUpdater extends AbstractFormUpdater
{
    protected string $sheetName = 'Variant form';
    protected string $tableName = 'variant_form';
    protected string $statisticName = 'variant_form';
}

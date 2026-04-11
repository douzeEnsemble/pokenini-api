<?php

declare(strict_types=1);

namespace App\Updater\Forms;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class VariantFormsUpdater extends AbstractFormsUpdater
{
    protected string $sheetName = 'Variant form';
    protected string $tableName = 'variant_form';
    protected string $statisticName = 'variant_forms';
}

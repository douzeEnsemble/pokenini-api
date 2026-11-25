<?php

declare(strict_types=1);

namespace App\Updater\Forms;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class VariantFormsUpdater extends AbstractFormsUpdater
{
    #[\Override]
    protected string $sheetName = 'Variant form';
    #[\Override]
    protected string $tableName = 'variant_form';
    #[\Override]
    protected string $statisticName = 'variant_forms';
}

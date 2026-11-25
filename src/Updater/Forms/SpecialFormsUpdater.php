<?php

declare(strict_types=1);

namespace App\Updater\Forms;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SpecialFormsUpdater extends AbstractFormsUpdater
{
    #[\Override]
    protected string $sheetName = 'Special form';
    #[\Override]
    protected string $tableName = 'special_form';
    #[\Override]
    protected string $statisticName = 'special_forms';
}

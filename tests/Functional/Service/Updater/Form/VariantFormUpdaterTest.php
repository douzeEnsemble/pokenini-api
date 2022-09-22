<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Updater\Form;

use App\Updater\AbstractUpdater;
use App\Updater\Form\VariantFormUpdater;

class VariantFormUpdaterTest extends AbstractFormUpdaterTest
{
    protected int $initialTotalCount = 7;
    protected int $finalTotalCount = 8;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'form / Variant form';
    protected string $tableName = 'variant_form';

    protected function getService(): AbstractUpdater
    {
        /** @var VariantFormUpdater */
        return static::getContainer()->get(VariantFormUpdater::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater\Forms;

use App\Updater\AbstractUpdater;
use App\Updater\Forms\VariantFormsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(VariantFormsUpdater::class)]
class VariantFormsUpdaterTest extends AbstractTestFormsUpdater
{
    protected int $initialTotalCount = 7;
    protected int $finalTotalCount = 8;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'form / Variant form';
    protected string $tableName = 'variant_form';

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var VariantFormsUpdater */
        return static::getContainer()->get(VariantFormsUpdater::class);
    }
}

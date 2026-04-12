<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater\Forms;

use App\Updater\AbstractUpdater;
use App\Updater\Forms\VariantFormsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(VariantFormsUpdater::class)]
final class VariantFormsUpdaterTest extends AbstractTestFormsUpdater
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
        return self::getContainer()->get(VariantFormsUpdater::class);
    }
}

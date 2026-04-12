<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater\Forms;

use App\Updater\AbstractUpdater;
use App\Updater\Forms\SpecialFormsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SpecialFormsUpdater::class)]
final class SpecialFormsUpdaterTest extends AbstractTestFormsUpdater
{
    protected int $initialTotalCount = 4;
    protected int $finalTotalCount = 5;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'form / Special form';
    protected string $tableName = 'special_form';

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var SpecialFormsUpdater */
        return self::getContainer()->get(SpecialFormsUpdater::class);
    }
}

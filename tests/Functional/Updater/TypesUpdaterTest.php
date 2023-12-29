<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\TypesUpdater;

class TypesUpdaterTest extends AbstractTestUpdater
{
    protected int $initialTotalCount = 19;
    protected int $finalTotalCount = 20;
    protected int $initialDeletedTotalCount = 1;
    protected int $mustBeDeletedTotalCount = 2;
    protected string $sheetName = 'Type';
    protected string $tableName = 'type';

    protected function getService(): AbstractUpdater
    {
        /** @var TypesUpdater */
        return static::getContainer()->get(TypesUpdater::class);
    }
}

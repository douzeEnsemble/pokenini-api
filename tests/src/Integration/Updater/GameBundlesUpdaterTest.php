<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\GameBundlesUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(GameBundlesUpdater::class)]
#[CoversClass(AbstractUpdater::class)]
class GameBundlesUpdaterTest extends AbstractTestUpdater
{
    protected int $initialTotalCount = 19;
    protected int $finalTotalCount = 19;
    protected int $mustBeDeletedTotalCount = 1;
    protected string $sheetName = 'Game Bundle';
    protected string $tableName = 'game_bundle';

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var GameBundlesUpdater */
        return static::getContainer()->get(GameBundlesUpdater::class);
    }
}

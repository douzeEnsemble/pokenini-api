<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\GameGenerationsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(GameGenerationsUpdater::class)]
#[CoversClass(AbstractUpdater::class)]
final class GameGenerationsUpdaterTest extends AbstractTestUpdater
{
    protected int $initialTotalCount = 9;
    protected int $finalTotalCount = 9;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Game Generation';
    protected string $tableName = 'game_generation';

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var GameGenerationsUpdater */
        return static::getContainer()->get(GameGenerationsUpdater::class);
    }
}

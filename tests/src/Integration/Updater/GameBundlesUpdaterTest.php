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
final class GameBundlesUpdaterTest extends AbstractTestNamedUpdater
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
        return self::getContainer()->get(GameBundlesUpdater::class);
    }

    #[\Override]
    protected function namesExample(): array
    {
        return [
            'slug' => 'redgreenblueyellow',
            'names' => ['en' => 'Red, Green, Blue, Yellow', 'fr' => 'Rouge, Vert, Bleu, Jaune'],
        ];
    }
}

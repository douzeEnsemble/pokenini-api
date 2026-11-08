<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\CollectionsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(CollectionsUpdater::class)]
#[CoversClass(AbstractUpdater::class)]
final class CollectionsUpdaterTest extends AbstractTestNamedUpdater
{
    protected int $initialTotalCount = 8;
    protected int $finalTotalCount = 8;
    protected int $initialDeletedTotalCount = 0;
    protected int $mustBeDeletedTotalCount = 0;
    protected string $sheetName = 'Collection';
    protected string $tableName = 'collection';

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var CollectionsUpdater */
        return self::getContainer()->get(CollectionsUpdater::class);
    }

    #[\Override]
    protected function namesExample(): array
    {
        return [
            'slug' => 'swshdynamaxadventuresbosses',
            'names' => ['en' => 'Sword, Shield - Dynamax Adventures bosses', 'fr' => 'Sword, Shield - Boss des expéditions Dynamax'],
        ];
    }
}

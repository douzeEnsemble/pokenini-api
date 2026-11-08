<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater;

use App\Updater\AbstractUpdater;
use App\Updater\TypesUpdater;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(TypesUpdater::class)]
#[CoversClass(AbstractUpdater::class)]
final class TypesUpdaterTest extends AbstractTestUpdater
{
    protected int $initialTotalCount = 19;
    protected int $finalTotalCount = 20;
    protected int $initialDeletedTotalCount = 1;
    protected int $mustBeDeletedTotalCount = 2;
    protected string $sheetName = 'Type';
    protected string $tableName = 'type';

    #[Test]
    public function executeStoresTheNameInEveryLanguage(): void
    {
        $this->getService()->execute($this->sheetName);

        $connection = self::getContainer()->get(Connection::class);

        $this->assertSame(
            '{"en": "Fighting", "fr": "Combat"}',
            $connection->executeQuery("SELECT names FROM type WHERE slug = 'fighting'")->fetchOne(),
        );
    }

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var TypesUpdater */
        return self::getContainer()->get(TypesUpdater::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;

/**
 * The tests of an updater which stores the name of its records in every language.
 */
abstract class AbstractTestNamedUpdater extends AbstractTestUpdater
{
    #[Test]
    public function executeStoresTheNameInEveryLanguage(): void
    {
        $example = $this->namesExample();
        $connection = self::getContainer()->get(Connection::class);

        // The record may already exist: its names must be written again, not only the first time
        $connection->executeStatement(
            "UPDATE {$this->tableName} SET names = '{}' WHERE slug = :slug",
            ['slug' => $example['slug']],
        );

        $this->getService()->execute($this->sheetName);

        $names = $connection->executeQuery(
            "SELECT names FROM {$this->tableName} WHERE slug = :slug",
            ['slug' => $example['slug']],
        )->fetchOne();

        $this->assertIsString($names);
        $this->assertSame($example['names'], json_decode($names, associative: true));
    }

    /**
     * A record of the sheet, with the names the updater must store.
     *
     * @return array{slug: string, names: array<string, string>}
     */
    abstract protected function namesExample(): array;
}

<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Exception\InvalidSheetDataException;
use App\Updater\AbstractUpdater;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractTestUpdater extends KernelTestCase
{
    use RefreshDatabaseTrait;

    protected int $initialTotalCount;
    protected int $finalTotalCount;
    protected int $initialDeletedTotalCount = 0;
    protected int $mustBeDeletedTotalCount;
    protected string $sheetName;
    protected string $tableName;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testDoEmptySheet(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage('Spreadsheet is empty');

        $service->execute('empty');
    }

    public function testDoWrongSheet(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage('This is not a valid data spreadsheet');

        $service->execute('wrong_sheet');
    }

    public function testDo(): void
    {
        $this->assertEquals($this->initialTotalCount, $this->getTableCount());
        $this->assertEquals($this->initialDeletedTotalCount, $this->getTableDeletedAtCount());

        $service = $this->getService();

        $service->execute($this->sheetName);

        $this->assertEquals($this->finalTotalCount, $this->getTableCount());
        $this->assertEquals($this->mustBeDeletedTotalCount, $this->getTableDeletedAtCount());
    }

    abstract protected function getService(): AbstractUpdater;

    protected function getTableCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            "SELECT COUNT(*) FROM {$this->tableName}"
        )->fetchOne();
    }

    protected function getTableDeletedAtCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            "SELECT COUNT(*) FROM {$this->tableName} WHERE deleted_at IS NOT NULL"
        )->fetchOne();
    }
}

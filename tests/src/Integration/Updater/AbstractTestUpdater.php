<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater;

use App\Exception\InvalidSheetDataException;
use App\Updater\AbstractUpdater;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\Test;
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

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function doEmptySheet(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessageIsOrContains('Spreadsheet is empty');

        $service->execute('empty');
    }

    #[Test]
    public function doWrongSheet(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessageIsOrContains('This is not a valid data spreadsheet');

        $service->execute('wrong_sheet');
    }

    #[Test]
    public function execute(): void
    {
        $this->assertEquals($this->initialTotalCount, $this->getTableCount());
        $this->assertEquals($this->initialDeletedTotalCount, $this->getTableDeletedAtCount());

        $service = $this->getService();

        $service->execute($this->sheetName);

        $this->assertEquals($this->finalTotalCount, $this->getTableCount());
        $this->assertEquals($this->mustBeDeletedTotalCount, $this->getTableDeletedAtCount());
    }

    #[Test]
    public function executeTwice(): void
    {
        $this->getService()->execute($this->sheetName);

        $firstCount = $this->getService()->getStatistic()->count;

        $this->getService()->execute($this->sheetName);

        $this->assertEquals(
            $firstCount,
            $this->getService()->getStatistic()->count
        );
    }

    abstract protected function getService(): AbstractUpdater;

    protected function getTableCount(): int
    {
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            "SELECT COUNT(*) FROM {$this->tableName}"
        )->fetchOne();
    }

    protected function getTableDeletedAtCount(): int
    {
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            "SELECT COUNT(*) FROM {$this->tableName} WHERE deleted_at IS NOT NULL"
        )->fetchOne();
    }
}

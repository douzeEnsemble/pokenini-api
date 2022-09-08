<?php

namespace functionnal\Service\Updater;

use App\Exception\InvalidSheetDataException;
use App\Service\Updater\AbstractUpdater;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractUpdaterTest extends KernelTestCase
{
    protected int $initialTotalCount;
    protected int $finalTotalCount;
    protected string $sheetName;
    protected string $tableName;

    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testDoEmptySheet(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage('Spreadsheet is empty');

        $service->do('empty');
    }

    public function testDoWrongSheet(): void
    {
        $service = $this->getService();

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage('This is not a valid data spreadsheet');

        $service->do('wrong_sheet');
    }

    public function testDo(): void
    {
        $this->assertEquals($this->initialTotalCount, $this->getTableCount());

        $service = $this->getService();

        $service->do($this->sheetName);

        $this->assertEquals($this->finalTotalCount, $this->getTableCount());
    }

    abstract protected function getService(): AbstractUpdater;

    protected function getTableCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery('SELECT COUNT(*) FROM '.$this->tableName)->fetchOne();
    }
}

<?php

namespace App\Service\Updater;

use App\Exception\InvalidSheetDataException;
use App\Service\SpreadsheetService;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\Exception as GoogleServiceException;

abstract class AbstractUpdater implements UpdaterInterface
{
    protected string $sheetName;
    protected string $tableName;
    protected string $headerCellsRange;
    /** @var string[] */
    protected array $recordsCellsRanges;

    public function __construct(
        protected readonly SpreadsheetService $spreadsheetService,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly string $spreadsheetId
    ) {
    }

    public function execute(?string $sheetName = null): void
    {
        $this->sheetName = $sheetName ?? $this->sheetName;

        $header = $this->getHeader();

        $this->validateHeader($header);

        $this->removeExistingRecords();

        foreach ($this->getRecordsCellsRanges() as $recordsCellsRange) {
            $this->handleCellRange($header, $recordsCellsRange);
        }
    }

    /**
     * @return string[]
     */
    abstract protected function getExpectedHeader(): array;

    /**
     * @param string[] $record
     *
     * @return void
     */
    abstract protected function upsertRecord(array $record): void;

    /**
     * @return string[]
     */
    protected function getRecordsCellsRanges(): array
    {
        return $this->recordsCellsRanges;
    }

    /**
     * @param string[] $header
     */
    protected function validateHeader(array $header): void
    {
        $expectedHeader = $this->getExpectedHeader();

        sort($header);
        sort($expectedHeader);

        if ($header !== $expectedHeader) {
            throw new InvalidSheetDataException('This is not a valid data spreadsheet');
        }
    }

    /**
     * @return string[]
     */
    protected function getHeader(): array
    {
        $values = $this->getSheetValues("'{$this->sheetName}'!{$this->headerCellsRange}");

        if (empty($values)) {
            throw new InvalidSheetDataException('Spreadsheet is empty');
        }

        return $values[0];
    }

    /**
     * @param string[] $header
     */
    protected function handleCellRange(array $header, string $cellRange): void
    {
        $records = $this->getRecords($header, $cellRange);
        $this->upsertRecords($records);
    }

    /**
     * @param string[] $header
     * @return string[][]
     */
    protected function getRecords(array $header, string $range): array
    {
        $values = $this->getRecordsData($range);

        return $this->transformRecords($values, $header);
    }

    /**
     * @param string $range
     *
     * @return string[][]
     */
    protected function getRecordsData(string $range): array
    {
        $values = $this->getSheetValues("'{$this->sheetName}'!{$range}");

        if (empty($values)) {
            throw new InvalidSheetDataException('There is not data in spreadsheet');
        }

        return $values;
    }

    /**
     * @param string[][] $values
     * @param string[] $header
     *
     * @return string[][]
     */
    protected function transformRecords(array $values, array $header): array
    {
        return array_map(static function ($value) use ($header): array {
            // To fill missing column at the end. The api remove empty data
            $value += array_fill(count($value), count($header) - count($value), '');

            return array_combine($header, $value);
        }, $values);
    }

    /**
     * @return string[][]
     */
    protected function getSheetValues(string $range): ?array
    {
        try {
            $response = $this->spreadsheetService->get($this->spreadsheetId, $range);

            if (null === $response) {
                return [];
            }

            return $response->getValues();
        } catch (GoogleServiceException $e) {
            throw new InvalidSheetDataException("Can't get data for range $range");
        }
    }

    /**
     * @param string[][] $records
     */
    protected function upsertRecords(array $records): void
    {
        array_walk($records, fn($record) => $this->upsertRecord($record));
    }

    protected function removeExistingRecords(): void
    {
        $tableName = $this->tableName;

        $sql = <<<SQL
        UPDATE  $tableName
        SET     deleted_at = NOW()
        SQL;

        $this->executeQuery($sql);
    }

    /**
     * @param mixed[] $sqlParameters
     */
    protected function executeQuery(string $sql, array $sqlParameters = []): void
    {
        $this->entityManager->getConnection()->executeStatement($sql, $sqlParameters);
    }
}

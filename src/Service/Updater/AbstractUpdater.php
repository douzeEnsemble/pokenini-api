<?php

namespace App\Service\Updater;

use App\Exception\InvalidSheetDataException;
use App\Service\SpreadsheetService;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\Exception as GoogleServiceException;

abstract class AbstractUpdater
{
    protected string $sheetName;
    protected string $tableName;
    protected string $headerCellsRange;
    protected string $recordsCellsRange;

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

        $records = $this->getRecords($header);

        $this->removeExistingRecords();

        $this->upsertRecords($records);
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
     * @return string[][]
     */
    protected function getRecords(array $header): array
    {
        $values = $this->getSheetValues("'{$this->sheetName}'!{$this->recordsCellsRange}");

        if (empty($values)) {
            throw new InvalidSheetDataException('There is not data in spreadsheet');
        }

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

        $this->entityManager->getConnection()->executeQuery($sql);
    }
}

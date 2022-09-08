<?php

namespace App\Service\Updater;

use App\Exception\InvalidSheetDataException;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Sheets;

abstract class AbstractUpdater
{
    protected string $sheetName;
    protected string $tableName;
    protected string $headerCellsRange;
    protected string $recordsCellsRange;

    private Sheets $service;

    public function __construct(
        protected readonly Client $client,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly string $spreadsheetId
    ) {
        $this->service = new Sheets($this->client);
    }

    public function do(?string $sheetName = null): void
    {
        $this->sheetName = $sheetName ?? $this->sheetName;

        $header = $this->getHeader();

        $this->validateHeader($header);

        $records = $this->getRecords($header);

        $this->upsertRecords($records);
    }

    abstract protected function getExpectedHeader(): array;
    abstract protected function upsertRecord(array $record): void;

    protected function validateHeader(array $header): void
    {
        $expectedHeader = $this->getExpectedHeader();

        sort($header);
        sort($expectedHeader);

        if ($header !== $expectedHeader) {
            throw new InvalidSheetDataException('This is not a valid data spreadsheet');
        }
    }

    protected function getHeader(): array
    {
        $values = $this->getSheetValues("'{$this->sheetName}'!{$this->headerCellsRange}");

        if (empty($values)) {
            throw new InvalidSheetDataException('Spreadsheet is empty');
        }

        return $values[0];
    }

    protected function getRecords(array $header)
    {
        $values = $this->getSheetValues("'{$this->sheetName}'!{$this->recordsCellsRange}");

        if (empty($values)) {
            throw new InvalidSheetDataException('There is not data in spreadsheet');
        }

        return array_map(static fn($value): array => array_combine($header, $value), $values);
    }

    protected function getSheetValues(string $range): ?array
    {
        try {
            $response = $this->service
                ->spreadsheets_values->get($this->spreadsheetId, $range);

            return $response->getValues();
        } catch (GoogleServiceException $e) {
            throw new InvalidSheetDataException("Can't get data for range $range");
        }
    }

    protected function upsertRecords(array $records): void
    {
        array_walk($records, fn($record) => $this->upsertRecord($record));
    }
}

<?php

declare(strict_types=1);

namespace App\Updater;

use App\DTO\DataChangeReport\Statistic;
use App\Exception\InvalidSheetDataException;
use App\Service\SpreadsheetService;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\Exception as GoogleServiceException;
use Psr\Log\LoggerInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class AbstractUpdater implements UpdaterInterface
{
    protected string $sheetName;
    protected string $tableName;
    protected string $statisticName;
    protected string $headerCellsRange;

    /** @var array<int, string> */
    protected array $recordsCellsRanges;

    protected Statistic $statistic;

    public function __construct(
        protected readonly SpreadsheetService $spreadsheetService,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly LoggerInterface $logger,
        protected readonly string $spreadsheetId
    ) {}

    #[\Override]
    public function execute(?string $sheetName = null): void
    {
        $this->statistic = new Statistic($this->statisticName);

        $this->sheetName = $sheetName ?? $this->sheetName;

        $header = $this->getHeader();

        $this->validateHeader($header);

        $this->removeExistingRecords();

        foreach ($this->getRecordsCellsRanges() as $recordsCellsRange) {
            $this->handleCellRange($header, $recordsCellsRange);
        }
    }

    #[\Override]
    public function getStatistic(): Statistic
    {
        return $this->statistic;
    }

    /**
     * @return string[]
     */
    abstract protected function getExpectedHeader(): array;

    /**
     * @param array<string, string> $record
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
     * @param array<int, string> $header
     */
    protected function validateHeader(array $header): void
    {
        $expectedHeader = $this->getExpectedHeader();

        sort($header);
        sort($expectedHeader);

        if ($header !== $expectedHeader) {
            $this->logger->error(
                'This is not a valid data spreadsheet',
                [
                    'header' => $header,
                    'expectedHeader' => $expectedHeader,
                ]
            );

            throw new InvalidSheetDataException('This is not a valid data spreadsheet');
        }
    }

    /**
     * @return array<int, string>
     */
    protected function getHeader(): array
    {
        $values = $this->getSheetValues("'{$this->sheetName}'!{$this->headerCellsRange}");

        if (null === $values || [] === $values) {
            $this->logger->error(
                'Spreadsheet is empty',
                [
                    'spreadsheet' => "'{$this->sheetName}'!{$this->headerCellsRange}",
                ]
            );

            throw new InvalidSheetDataException('Spreadsheet is empty');
        }

        return $values[0];
    }

    /**
     * @param array<int, string> $header
     */
    protected function handleCellRange(array $header, string $cellRange): void
    {
        $records = $this->getRecords($header, $cellRange);
        $this->upsertRecords($records);
    }

    /**
     * @param array<int, string> $header
     *
     * @return array<int, array<string, string>>
     */
    protected function getRecords(array $header, string $range): array
    {
        $values = $this->getRecordsData($range);

        return $this->transformRecords($values, $header);
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function getRecordsData(string $range): array
    {
        $values = $this->getSheetValues("'{$this->sheetName}'!{$range}");

        if (null === $values || [] === $values) {
            $this->logger->error(
                'There is not data in spreadsheet',
                [
                    'spreadsheet' => "'{$this->sheetName}'!{$range}",
                ]
            );

            throw new InvalidSheetDataException("There is not data in spreadsheet ('{$this->sheetName}'!{$range})");
        }

        return $values;
    }

    /**
     * @param array<int, array<int, string>> $values
     * @param array<int, string>             $header
     *
     * @return array<int, array<string, string>>
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
     * @return array<int, array<int, string>>
     */
    protected function getSheetValues(string $range): ?array
    {
        try {
            $response = $this->spreadsheetService->get($this->spreadsheetId, $range);

            if (null === $response) {
                // This condition is here form safety reason
                // It can never happen
                // @codeCoverageIgnoreStart
                return [];
                // @codeCoverageIgnoreEnd
            }

            /** @var array<int, array<int, string>> */
            return $response->getValues();
        } catch (GoogleServiceException $e) {
            $this->logger->error(
                "Can't get data for range {$range}",
                [
                    'exception' => $e,
                ]
            );

            throw new InvalidSheetDataException("Can't get data for range {$range}");
        }
    }

    /**
     * @param array<int, array<string, string>> $records
     */
    protected function upsertRecords(array $records): void
    {
        foreach ($records as $record) {
            $this->upsertRecord($record);
        }
    }

    protected function removeExistingRecords(): void
    {
        $tableName = $this->tableName;

        $sql = <<<SQL
            UPDATE  {$tableName}
            SET     deleted_at = NOW()
            WHERE   deleted_at IS NULL
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

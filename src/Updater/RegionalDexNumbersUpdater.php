<?php

declare(strict_types=1);

namespace App\Updater;

use App\Exception\InvalidSheetDataException;
use App\Helper\A1Notation;
use App\Repository\RegionsRepository;
use App\Service\SpreadsheetService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class RegionalDexNumbersUpdater extends AbstractUpdater
{
    protected const int RANGE_SIZE = 100;
    protected const int BATCH_SIZE = 20;
    protected string $sheetName = 'Regional Dex Number';
    protected string $tableName = 'regional_dex_number';
    protected string $statisticName = 'regional_dex_numbers';
    protected int $recordsCellsStartRowIndex = 1;
    protected int $recordsCellsStartColumnIndex = 0;

    /**
     * @var null|string[]
     */
    private ?array $regionsCache = null;

    /** @var string[][] */
    private array $records;

    public function __construct(
        SpreadsheetService $spreadsheetService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        string $spreadsheetId,
        protected readonly RegionsRepository $regionsRepository
    ) {
        parent::__construct(
            $spreadsheetService,
            $entityManager,
            $logger,
            $spreadsheetId
        );
    }

    /**
     * @return string[]
     */
    #[\Override]
    protected function getRecordsCellsRanges(): array
    {
        $rowCount = $this->spreadsheetService->getSheetRowCount($this->spreadsheetId, $this->sheetName);
        $columnCount = $this->spreadsheetService->getSheetColumnCount($this->spreadsheetId, $this->sheetName);

        $nbBatch = ($rowCount / self::RANGE_SIZE);

        $ranges = [];
        for ($i = 0; $i < $nbBatch; ++$i) {
            $startRow = $this->recordsCellsStartRowIndex + (self::RANGE_SIZE * $i);
            $endRow = min($startRow + self::RANGE_SIZE - 1, $rowCount - 1);

            $ranges[] = sprintf(
                '%s:%s',
                A1Notation::fromIndex($startRow, $this->recordsCellsStartColumnIndex),
                A1Notation::fromIndex($endRow, $this->recordsCellsStartColumnIndex + $columnCount - 1),
            );
        }

        return $ranges;
    }

    /**
     * @return string[]
     */
    #[\Override]
    protected function getHeader(): array
    {
        $regions = $this->getRegions();

        $headerCellsRange = sprintf(
            '%s:%s',
            A1Notation::fromIndex(
                0,
                $this->recordsCellsStartColumnIndex
            ),
            A1Notation::fromIndex(
                0,
                1 + count($regions)
            ),
        );

        $values = $this->getSheetValues("'{$this->sheetName}'!{$headerCellsRange}");

        if (!$values) {
            throw new InvalidSheetDataException('Spreadsheet is empty');
        }

        return $values[0];
    }

    #[\Override]
    protected function getExpectedHeader(): array
    {
        $regions = $this->getRegions();

        return array_merge(
            [
                '#Pokemon',
                'National',
            ],
            $regions
        );
    }

    #[\Override]
    protected function getRecords(array $header, string $range): array
    {
        $values = $this->getRecordsData($range);
        $newValues = $this->transformRecords($values, $header);
        unset($values);

        $this->getFromRecords($newValues);

        return $this->records;
    }

    /**
     * @param string[] $header
     */
    #[\Override]
    protected function handleCellRange(array $header, string $cellRange): void
    {
        $records = $this->getRecords($header, $cellRange);

        $availabilitiesChunks = array_chunk($records, self::BATCH_SIZE);
        unset($records);
        foreach ($availabilitiesChunks as $chunk) {
            $this->upsertRecords($chunk);
        }
    }

    /**
     * @param bool[][]|string[][] $records
     */
    #[\Override]
    protected function upsertRecords(array $records): void
    {
        $sqlValues = [];
        $sqlParameters = [];
        $index = 0;
        foreach ($records as $record) {
            $sqlValues[] = ":id{$index}"
                .", :pokemonSlug{$index}"
                .", (SELECT id FROM region WHERE slug = :regionSlug{$index})"
                .", :dexNumber{$index}";

            $sqlParameters["id{$index}"] = Uuid::v4();
            $sqlParameters["pokemonSlug{$index}"] = $record['pokemonSlug'];
            $sqlParameters["regionSlug{$index}"] = $record['regionSlug'];
            $sqlParameters["dexNumber{$index}"] = $record['dexNumber'];

            ++$index;
        }

        $sqlValuesStr = implode('), (', $sqlValues);

        $sql = <<<SQL
                    INSERT INTO regional_dex_number (
                        id,
                        pokemon_slug,
                        region_id,
                        dex_number
                    )
                    VALUES ({$sqlValuesStr})
            SQL;

        $this->executeQuery($sql, $sqlParameters);

        $this->statictic->incrementBy($index);
    }

    // @codeCoverageIgnoreStart
    #[\Override]
    protected function upsertRecord(array $record): void
    {
        throw new \RuntimeException(
            "Don't use this method."
        );
    }
    // @codeCoverageIgnoreEnd

    #[\Override]
    protected function removeExistingRecords(): void
    {
        $tableName = $this->tableName;

        $sql = <<<SQL
                DELETE FROM {$tableName}
            SQL;

        $this->entityManager->getConnection()->executeQuery($sql);
    }

    /**
     * @param string[][] $records
     */
    private function getFromRecords(array $records): void
    {
        $this->records = [];
        foreach ($records as $record) {
            $this->transformRecord($record);
        }
    }

    /**
     * @param string[] $record
     */
    private function transformRecord(
        array $record
    ): void {
        $slug = $record['#Pokemon'];
        unset($record['#Pokemon'], $record['National']);

        foreach ($record as $regionSlug => $dexNumber) {
            if (!is_numeric($dexNumber)) {
                continue;
            }

            $this->records[] = [
                'pokemonSlug' => $slug,
                'regionSlug' => $regionSlug,
                'dexNumber' => $dexNumber,
            ];
        }
    }

    /**
     * @return string[]
     */
    private function getRegions(): array
    {
        if (null != $this->regionsCache) {
            return $this->regionsCache;
        }

        $this->regionsCache = $this->regionsRepository->getAllSlugs();

        return $this->regionsCache;
    }
}

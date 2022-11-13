<?php

declare(strict_types=1);

namespace App\Updater;

use App\Helper\A1Notation;
use Symfony\Component\Uid\Uuid;

class GameAvailabilityUpdater extends AbstractUpdater
{
    protected string $sheetName = 'Games Availability';
    protected string $tableName = 'game_availability';
    protected string $headerCellsRange = 'A2:AL2';
    protected int $recordsCellsStartRowIndex = 2;
    protected int $recordsCellsStartColumnIndex = 0;

    protected const RANGE_SIZE = 100;
    protected const BATCH_SIZE = 20;

    private int $count = 0;

    /** @var string[][] */
    private array $gameAvailabilities;

    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return string[]
     */
    protected function getRecordsCellsRanges(): array
    {
        $rowCount = $this->spreadsheetService->getSheetRowCount($this->spreadsheetId, $this->sheetName);
        $columnCount = $this->spreadsheetService->getSheetColumnCount($this->spreadsheetId, $this->sheetName);

        $nbBatch = ($rowCount / self::RANGE_SIZE);

        $ranges = [];
        for ($i = 0; $i < $nbBatch; $i++) {
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

    protected function getExpectedHeader(): array
    {
        return [
            '#',
            'Name',
            'Red',
            'Green',
            'Blue',
            'Yellow',
            'Gold',
            'Silver',
            'Crystal',
            'Ruby',
            'Sapphire',
            'Red Fire',
            'Leaf Green',
            'Emerald',
            'Diamond',
            'Pearl',
            'Platinium',
            'Heart Gold',
            'Soul Silver',
            'Black',
            'White',
            'Black 2',
            'White 2',
            'X',
            'Y',
            'Omega Ruby',
            'Alpha Sapphire',
            'Sun',
            'Moon',
            'Ultra Sun',
            'Ultra Moon',
            'Let\'s Go Pikachu',
            'Let\'s Go Eevee',
            'Sword',
            'Shield',
            'Brillant Diamond',
            'Shining Pearl',
            'Pokémon Legends Arceus',
        ];
    }

    protected function getRecords(array $header, string $range): array
    {
        $values = $this->getRecordsData($range);
        $newValues = $this->transformRecords($values, $header);
        unset($values);

        $this->getGameAvailabilitiesFromRecords($newValues);

        return $this->gameAvailabilities;
    }

    /**
     * @param string[] $header
     */
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
     * @param string[][]|bool[][] $records
     */
    protected function upsertRecords(array $records): void
    {
        if (empty($records)) {
            return;
        }

        $sqlValues = [];
        $sqlParameters = [];
        $index = 0;
        foreach ($records as $gameAvailability) {
            $sqlValues[] = ":id$index"
                . ", :pokemonName$index"
                . ", (SELECT id FROM game WHERE name = :game$index)"
                . ", :availability$index"
            ;

            $sqlParameters["id$index"] = Uuid::v4();
            $sqlParameters["pokemonName$index"] = $gameAvailability['pokemonName'];
            $sqlParameters["game$index"] = $gameAvailability['game'];
            $sqlParameters["availability$index"] = $gameAvailability['availability'];

            $index++;
        }

        $sqlValuesStr = implode('), (', $sqlValues);

        $sql = <<<SQL
        INSERT INTO game_availability (
            id,
            pokemon_name,
            game_id,
            availability
        )
        VALUES ($sqlValuesStr)
SQL;

        $this->executeQuery($sql, $sqlParameters);

        $this->count += $index;
    }

    protected function upsertRecord(array $record): void
    {
        throw new \RuntimeException(
            "Don't use this method. Use \App\Service\Updater\GameAvailabilityUpdater::insertGameAvailabilities"
        );
    }

    protected function removeExistingRecords(): void
    {
        $tableName = $this->tableName;

        $sql = <<<SQL
            DELETE FROM $tableName
        SQL;

        $this->entityManager->getConnection()->executeQuery($sql);
    }

    /**
     * @param string[][] $records
     */
    private function getGameAvailabilitiesFromRecords(array $records): void
    {
        $this->gameAvailabilities = [];
        foreach ($records as $record) {
            $this->transformGameAvailabilityRecord($record);
        }
    }

    /**
     * @param string[]   $record
     */
    private function transformGameAvailabilityRecord(
        array $record
    ): void {
        unset($record['#']);

        $name = $record['Name'];
        unset($record['Name']);

        foreach ($record as $game => $availability) {
            $this->gameAvailabilities[] = [
                'pokemonName' => $name,
                'game' => $game,
                'availability' => $availability,
            ];
        }
    }
}

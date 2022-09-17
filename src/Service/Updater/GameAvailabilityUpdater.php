<?php

namespace App\Service\Updater;

use App\Helper\A1Notation;
use Google\Service\Sheets\BandedRange;
use Google\Service\Sheets\CellData;
use Google\Service\Sheets\GridRange;
use Symfony\Component\Uid\Uuid;

class GameAvailabilityUpdater extends AbstractUpdater
{
    protected string $sheetName = 'Games Availability';
    protected string $tableName = 'game_availability';
    protected string $headerCellsRange = 'A2:AL2';
    protected int $recordsCellsStartRowIndex = 2;
    protected int $recordsCellsStartColumnIndex = 0;

    protected const BATCH_SIZE = 100;

    /**
     * @return string[]
     */
    protected function getRecordsCellsRanges(): array
    {
        $rowCount = $this->spreadsheetService->getSheetRowCount($this->spreadsheetId, $this->sheetName);
        $columnCount = $this->spreadsheetService->getSheetColumnCount($this->spreadsheetId, $this->sheetName);

        $nbBatch = ($rowCount / self::BATCH_SIZE);

        $ranges = [];
        for ($i = 0; $i < $nbBatch; $i++) {
            $startRow = $this->recordsCellsStartRowIndex + (self::BATCH_SIZE * $i) + 1;
            $endRow = min($startRow + self::BATCH_SIZE - 1, $rowCount - 1);

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

        return $this->getGameAvailabilitiesFromRecords($newValues);
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

        $this->entityManager->getConnection()->executeQuery($sql, $sqlParameters);
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
     *
     * @return string[][]
     */
    private function getGameAvailabilitiesFromRecords(array $records): array
    {
        $gameAvailabilities = [];
        foreach ($records as $record) {
            $this->transformGameAvailabilityRecord($gameAvailabilities, $record);
        }

        return $gameAvailabilities;
    }

    /**
     * @param string[][] $gameAvailabilities
     * @param string[]   $record
     */
    private function transformGameAvailabilityRecord(
        array &$gameAvailabilities,
        array $record
    ): void {
        unset($record['#']);

        $name = $record['Name'];
        unset($record['Name']);

        foreach ($record as $game => $availability) {
            $gameAvailabilities[] = [
                'pokemonName' => $name,
                'game' => $game,
                'availability' => $availability,
            ];
        }
    }
}

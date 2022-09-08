<?php

namespace App\Service\Updater;

use Symfony\Component\Uid\Uuid;

class GameGenerationUpdater extends AbstractUpdater
{
    protected string $sheetName = 'Game Generation';
    protected string $tableName = 'game_generation';
    protected string $headerCellsRange = 'A1:B1';
    protected string $recordsCellsRange = 'A2:B';

    protected function getExpectedHeader(): array
    {
        return [
            'Slug',
            'Name',
        ];
    }

    protected function upsertRecord(array $record): void
    {
        if (empty($record)) {
            return;
        }

        $sqlParameters = [
            'id' => (string) Uuid::v4(),
            'slug' => $record['Slug'],
            'name' => $record['Name'],
        ];

        $tableName = $this->tableName;

        $sql = <<<SQL
        INSERT INTO $tableName(
          id,
          slug,
          name
        )
        VALUES (
            :id,
            :slug,
            :name
        )
        ON CONFLICT (slug)
        DO
        UPDATE
        SET
            name = excluded.name
        SQL;

        $this->entityManager->getConnection()->executeQuery($sql, $sqlParameters);
    }
}

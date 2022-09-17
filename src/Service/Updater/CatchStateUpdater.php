<?php

namespace App\Service\Updater;

use Symfony\Component\Uid\Uuid;

class CatchStateUpdater extends AbstractUpdater
{
    protected string $sheetName = 'Catch state';
    protected string $tableName = 'catch_state';
    protected string $headerCellsRange = 'A1:D1';
    protected array $recordsCellsRanges = ['A2:D'];

    protected function getExpectedHeader(): array
    {
        return [
            'Slug',
            'Name',
            'French Name',
            'Order',
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
            'french_name' => $record['French Name'],
            'order_number' => $record['Order'],
        ];

        $tableName = $this->tableName;

        $sql = <<<SQL
        INSERT INTO $tableName(
          id,
          slug,
          name,
          french_name,
          order_number
        )
        VALUES (
            :id,
            :slug,
            :name,
            :french_name,
            :order_number
        )
        ON CONFLICT (slug)
        DO
        UPDATE
        SET
            name = excluded.name,
            french_name = excluded.french_name,
            order_number= excluded.order_number,
            deleted_at = NULL
        SQL;

        $this->entityManager->getConnection()->executeQuery($sql, $sqlParameters);
    }
}

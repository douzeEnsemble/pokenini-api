<?php

declare(strict_types=1);

namespace App\Updater\Form;

use App\Updater\AbstractUpdater;
use Symfony\Component\Uid\Uuid;

abstract class AbstractFormUpdater extends AbstractUpdater
{
    protected string $headerCellsRange = 'A1:C1';
    protected array $recordsCellsRanges = ['A2:C'];

    protected function getExpectedHeader(): array
    {
        return [
            'Name',
            'Slug',
            'Order'
        ];
    }

    protected function upsertRecord(array $record): void
    {
        if (empty($record) || empty($record['Slug'])) {
            return;
        }

        $sqlParameters = [
            'id' => (string) Uuid::v4(),
            'slug' => $record['Slug'],
            'name' => $record['Name'],
            'order_number' => $record['Order'],
        ];

        $tableName = $this->tableName;

        $sql = <<<SQL
        INSERT INTO $tableName(
          id,
          slug,
          name,
          order_number
        )
        VALUES (
            :id,
            :slug,
            :name,
            :order_number
        )
        ON CONFLICT (slug)
        DO
        UPDATE
        SET
            name = excluded.name,
            order_number= excluded.order_number,
            deleted_at = NULL
        SQL;

        $this->executeQuery($sql, $sqlParameters);

        $this->statictic->increment();
    }
}

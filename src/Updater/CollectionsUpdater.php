<?php

declare(strict_types=1);

namespace App\Updater;

use Symfony\Component\Uid\Uuid;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CollectionsUpdater extends AbstractUpdater
{
    protected string $sheetName = 'Collection';
    protected string $tableName = 'collection';
    protected string $statisticName = 'collections';
    protected string $headerCellsRange = 'A1:D1';

    /** @var string[] */
    protected array $recordsCellsRanges = ['A2:D'];

    #[\Override]
    protected function getExpectedHeader(): array
    {
        return [
            'Name',
            'French Name',
            'Slug',
            'Order',
        ];
    }

    #[\Override]
    protected function upsertRecord(array $record): void
    {
        $sqlParameters = [
            'id' => (string) Uuid::v4(),
            'slug' => $record['Slug'],
            'name' => $record['Name'],
            'order_number' => $record['Order'],
            'french_name' => $record['French Name'],
        ];

        $tableName = $this->tableName;

        $sql = <<<SQL
            INSERT INTO {$tableName}(
              id,
              slug,
              name,
              order_number,
              french_name
            )
            VALUES (
                :id,
                :slug,
                :name,
                :order_number,
                :french_name
            )
            ON CONFLICT (slug)
            DO
            UPDATE
            SET
                name = excluded.name,
                order_number = excluded.order_number,
                french_name = excluded.french_name,
                deleted_at = NULL
            SQL;

        $this->executeQuery($sql, $sqlParameters);

        $this->statictic->increment();
    }
}

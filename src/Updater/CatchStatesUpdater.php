<?php

declare(strict_types=1);

namespace App\Updater;

use Symfony\Component\Uid\Uuid;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CatchStatesUpdater extends AbstractUpdater
{
    protected string $sheetName = 'Catch state';
    protected string $tableName = 'catch_state';
    protected string $statisticName = 'catch_states';
    protected string $headerCellsRange = 'A1:F1';

    /** @var array<int, string> */
    protected array $recordsCellsRanges = ['A2:F'];

    #[\Override]
    protected function getExpectedHeader(): array
    {
        return [
            'Slug',
            'Name',
            'French Name',
            'Order',
            'Color',
            'Text Color',
        ];
    }

    #[\Override]
    protected function upsertRecord(array $record): void
    {
        $sqlParameters = [
            'id' => (string) Uuid::v4(),
            'slug' => $record['Slug'],
            'name' => $record['Name'],
            'french_name' => $record['French Name'],
            'names' => $this->namesParameter($record),
            'order_number' => $record['Order'],
            'color' => $record['Color'],
            'text_color' => $record['Text Color'],
        ];

        $tableName = $this->tableName;

        $sql = <<<SQL
            INSERT INTO {$tableName}(
              id,
              slug,
              name,
              french_name,
              names,
              order_number,
              color,
              text_color
            )
            VALUES (
                :id,
                :slug,
                :name,
                :french_name,
                CAST(:names AS jsonb),
                :order_number,
                :color,
                :text_color
            )
            ON CONFLICT (slug)
            DO
            UPDATE
            SET
                name = excluded.name,
                french_name = excluded.french_name,
                names = excluded.names,
                order_number= excluded.order_number,
                color= excluded.color,
                text_color= excluded.text_color,
                deleted_at = NULL
            SQL;

        $this->executeQuery($sql, $sqlParameters);

        $this->statistic->increment();
    }
}

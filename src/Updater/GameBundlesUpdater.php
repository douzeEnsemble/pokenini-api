<?php

declare(strict_types=1);

namespace App\Updater;

use Symfony\Component\Uid\Uuid;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GameBundlesUpdater extends AbstractUpdater
{
    #[\Override]
    protected string $sheetName = 'Game Bundle';
    #[\Override]
    protected string $tableName = 'game_bundle';
    #[\Override]
    protected string $statisticName = 'game_bundles';
    #[\Override]
    protected string $headerCellsRange = 'A1:E1';

    /** @var array<int, string> */
    #[\Override]
    protected array $recordsCellsRanges = ['A2:E'];

    #[\Override]
    protected function getExpectedHeader(): array
    {
        return [
            '#Generation',
            'Slug',
            'Name',
            'French Name',
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
            'french_name' => $record['French Name'],
            'names' => $this->namesParameter($record),
            'generation' => $record['#Generation'],
            'order' => $record['Order'],
        ];

        $tableName = $this->tableName;

        $sql = <<<SQL
            INSERT INTO {$tableName}(
              id,
              slug,
              name,
              french_name,
              names,
              generation_id,
              order_number
            )
            VALUES (
                :id,
                :slug,
                :name,
                :french_name,
                CAST(:names AS jsonb),
                (SELECT id FROM game_generation WHERE slug = :generation),
                :order
            )
            ON CONFLICT (slug)
            DO
            UPDATE
            SET
                name = excluded.name,
                french_name = excluded.french_name,
                names = excluded.names,
                order_number = excluded.order_number,
                generation_id = excluded.generation_id,
                deleted_at = NULL
            SQL;

        $this->executeQuery($sql, $sqlParameters);

        $this->statistic->increment();
    }
}

<?php

declare(strict_types=1);

namespace App\Updater;

use Symfony\Component\Uid\Uuid;

class GamesUpdater extends AbstractUpdater
{
    protected string $sheetName = 'Game';
    protected string $tableName = 'game';
    protected string $statisticName = 'games';
    protected string $headerCellsRange = 'A1:D1';

    /** @var string[] */
    protected array $recordsCellsRanges = ['A2:D'];

    #[\Override]
    protected function getExpectedHeader(): array
    {
        return [
            '#Game Bundle',
            'Name',
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
            'game_bundle_slug' => $record['#Game Bundle'],
        ];

        $tableName = $this->tableName;

        $sql = <<<SQL
            INSERT INTO {$tableName}(
              id,
              slug,
              name,
              order_number,
              bundle_id
            )
            VALUES (
                :id,
                :slug,
                :name,
                :order_number,
                (SELECT id FROM game_bundle WHERE slug = :game_bundle_slug)
            )
            ON CONFLICT (slug)
            DO
            UPDATE
            SET
                name = excluded.name,
                order_number = excluded.order_number,
                bundle_id = excluded.bundle_id,
                deleted_at = NULL
            SQL;

        $this->executeQuery($sql, $sqlParameters);

        $this->statictic->increment();
    }
}

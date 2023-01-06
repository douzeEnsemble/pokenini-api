<?php

declare(strict_types=1);

namespace App\Updater;

use Symfony\Component\Uid\Uuid;

class GameBundlesUpdater extends AbstractUpdater
{
    protected string $sheetName = 'Game Bundle';
    protected string $tableName = 'game_bundle';
    protected string $statisticName = 'game_bundles';
    protected string $headerCellsRange = 'A1:D1';
    /** @var string[] */
    protected array $recordsCellsRanges = ['A2:D'];

    protected function getExpectedHeader(): array
    {
        return [
            '#Generation',
            'Slug',
            'Name',
            'Order',
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
            'generation' => $record['#Generation'],
            'order' => $record['Order'],
        ];

        $tableName = $this->tableName;

        $sql = <<<SQL
        INSERT INTO $tableName(
          id,
          slug,
          name,
          generation_id,
          order_number
        )
        VALUES (
            :id,
            :slug,
            :name,
            (SELECT id FROM game_generation WHERE slug = :generation),
            :order
        )
        ON CONFLICT (slug)
        DO
        UPDATE
        SET
            name = excluded.name,
            order_number = excluded.order_number,
            generation_id = excluded.generation_id,
            deleted_at = NULL
        SQL;

        $this->executeQuery($sql, $sqlParameters);

        $this->statictic->increment();
    }
}

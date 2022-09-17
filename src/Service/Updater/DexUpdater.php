<?php

namespace App\Service\Updater;

use Symfony\Component\Uid\Uuid;

class DexUpdater extends AbstractUpdater
{
    protected string $sheetName = 'Dex';
    protected string $tableName = 'dex';
    protected string $headerCellsRange = 'A1:H1';
    protected array $recordsCellsRanges = ['A2:H'];

    protected function getExpectedHeader(): array
    {
        return [
            'Slug',
            'Name',
            'French Name',
            'Order',
            'Selection rule',
            'Is Shiny',
            'Is Private',
            'Is Display Form',
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
            'selection_rule' => $record['Selection rule'],
            'is_shiny' => $record['Is Shiny'],
            'is_private' => $record['Is Private'],
            'is_display_form' => $record['Is Display Form'],
        ];

        $tableName = $this->tableName;

        $sql = <<<SQL
        INSERT INTO $tableName(
          id,
          slug,
          name,
          french_name,
          order_number,
          selection_rule,
          is_shiny,
          is_private,
          is_display_form
        )
        VALUES (
            :id,
            :slug,
            :name,
            :french_name,
            :order_number,
            :selection_rule,
            :is_shiny,
            :is_private,
            :is_display_form
        )
        ON CONFLICT (slug)
        DO
        UPDATE
        SET
            name = excluded.name,
            french_name = excluded.french_name,
            order_number= excluded.order_number,
            selection_rule= excluded.selection_rule,
            is_shiny= excluded.is_shiny,
            is_private= excluded.is_private,
            is_display_form= excluded.is_display_form,
            deleted_at = NULL
        SQL;

        $this->entityManager->getConnection()->executeQuery($sql, $sqlParameters);
    }
}

<?php

namespace App\Service\Updater\Form;

use App\Exception\InvalidSheetDataException;
use App\Service\Updater\AbstractUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Sheets;
use Symfony\Component\Uid\Uuid;

abstract class AbstractFormUpdater extends AbstractUpdater
{
    protected string $headerCellsRange = 'A1:C1';
    protected string $recordsCellsRange = 'A2:C';

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
        if (empty($record)) {
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
            order_number= excluded.order_number
        SQL;

        $this->entityManager->getConnection()->executeQuery($sql, $sqlParameters);
    }
}

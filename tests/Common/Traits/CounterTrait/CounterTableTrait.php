<?php

namespace App\Tests\Common\Traits\CounterTrait;

use Doctrine\DBAL\Connection;

trait CounterTableTrait
{
    protected function getTableCount(string $tableName): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            "SELECT COUNT(*) FROM {$tableName}"
        )->fetchOne();
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\CounterTrait;

use Doctrine\DBAL\Connection;

trait CountActionLogTrait
{
    protected function getActionLogCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            'SELECT COUNT(*) FROM action_log'
        )->fetchOne();
    }

    protected function getActionLogToProcessCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            'SELECT COUNT(*) FROM action_log WHERE done_at IS NULL'
        )->fetchOne();
    }

    protected function getActionLogDoneCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            'SELECT COUNT(*) FROM action_log WHERE done_at IS NOT NULL'
        )->fetchOne();
    }
}

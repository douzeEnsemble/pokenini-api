<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\CounterTrait;

use Doctrine\DBAL\Connection;

trait CountActionLogTrait
{
    protected function getActionLogCount(): int
    {
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            'SELECT COUNT(*) FROM action_log'
        )->fetchOne();
    }

    protected function getActionLogToProcessCount(): int
    {
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            'SELECT COUNT(*) FROM action_log WHERE done_at IS NULL'
        )->fetchOne();
    }

    protected function getActionLogDoneCount(): int
    {
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            'SELECT COUNT(*) FROM action_log WHERE done_at IS NOT NULL'
        )->fetchOne();
    }

    protected function getActionLogErrorCount(): int
    {
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            'SELECT COUNT(*) FROM action_log WHERE error_trace IS NOT NULL'
        )->fetchOne();
    }
}

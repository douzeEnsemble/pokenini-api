<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\CounterTrait;

use Doctrine\DBAL\Connection;

trait CountMessengerActionTrait
{
    protected function getMessengerActionCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            'SELECT COUNT(*) FROM messenger_action'
        )->fetchOne();
    }

    protected function getMessengerActionToProcessCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            'SELECT COUNT(*) FROM messenger_action WHERE done_at IS NULL'
        )->fetchOne();
    }

    protected function getMessengerActionDoneCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery(
            'SELECT COUNT(*) FROM messenger_action WHERE done_at IS NOT NULL'
        )->fetchOne();
    }
}

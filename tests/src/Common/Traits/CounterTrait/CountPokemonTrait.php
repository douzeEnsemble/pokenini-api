<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\CounterTrait;

use Doctrine\DBAL\Connection;

trait CountPokemonTrait
{
    protected function getPokemonCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery('SELECT COUNT(*) FROM pokemon')->fetchOne();
    }

    protected function getPokemonNotDeletedCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery('SELECT COUNT(*) FROM pokemon WHERE deleted_at IS NULL')->fetchOne();
    }

    protected function getPokemonDeletedCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery('SELECT COUNT(*) FROM pokemon WHERE deleted_at IS NOT NULL')->fetchOne();
    }
}

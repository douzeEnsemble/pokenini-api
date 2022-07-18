<?php

namespace App\Tests\resources\functionnal\CounterTrait;

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

    /**
     * @return string[]
     */
    protected function getPokemonFromName(string $name): array
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        $sql = 'SELECT * FROM pokemon WHERE name = :name';
        $parameters = ['name' => $name];

        /** @var false|string[] $result */
        $result = $connection->executeQuery($sql, $parameters)->fetchAssociative();

        if (false === $result) {
            return [];
        }

        return $result;
    }
}

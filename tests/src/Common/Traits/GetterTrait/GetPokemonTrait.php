<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\GetterTrait;

use Doctrine\DBAL\Connection;

trait GetPokemonTrait
{
    /**
     * @return bool[]|null[]|string[]
     */
    protected function getPokemonFromName(string $name): array
    {
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

<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\HasserTrait;

use Doctrine\DBAL\Connection;

trait HasPokemonAvailabilitiesTrait
{
    protected function hasPokemonAvailabilities(string $category, string $pokemonName): bool
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        $sql = <<< SQL
        SELECT  COUNT(*)
        FROM    pokemon_availabilities AS pa
            JOIN pokemon AS p
                ON pa.pokemon_id = p.id
        WHERE   pa.category = :category
            AND p.name = :pokemon_name
        SQL;

        /** @var int $count */
        $count = $connection->executeQuery(
            $sql,
            [
                'category' => $category,
                'pokemon_name' => $pokemonName,
            ]
        )->fetchOne();

        return $count != 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Resources\Traits\GetterTrait;

use Doctrine\DBAL\Connection;

trait GetPokedexTrait
{
    /**
     * @return string[]
     */
    protected function getPokedexFromSlugs(string $dexSlug, string $pokemonSlug): array
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        $sql = <<<'SQL'
        SELECT      cs.*
        FROM        pokedex AS pd
            JOIN pokemon AS p
                ON pd.pokemon_id = p.id AND p.slug = :pokemon_slug
            JOIN dex AS d
                ON pd.dex_id = d.id AND d.slug = :dex_slug
            JOIN catch_state AS cs
                ON pd.catch_state_id = cs.id
        SQL;
        $parameters = [
            'dex_slug' => $dexSlug,
            'pokemon_slug' => $pokemonSlug,
        ];

        /** @var false|string[] $result */
        $result = $connection->executeQuery($sql, $parameters)->fetchAssociative();

        if (false === $result) {
            return [];
        }

        return $result;
    }
}

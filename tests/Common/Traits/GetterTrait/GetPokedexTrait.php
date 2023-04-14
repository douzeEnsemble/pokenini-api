<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\GetterTrait;

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
            JOIN trainer_dex AS td
                ON pd.trainer_dex_id = td.id
            JOIN catch_state AS cs
                ON pd.catch_state_id = cs.id
        WHERE   td.slug = :dex_slug
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

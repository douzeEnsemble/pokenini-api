<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\GetterTrait;

use Doctrine\DBAL\Connection;

trait GetTrainerPokemonEloTrait
{
    /**
     * @return int[]|string[]
     */
    protected function getEloAndCounts(
        string $trainerExternalId,
        string $dexSlug,
        string $electionSlug,
        string $pokemonSlug
    ): array {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        $sql = <<< 'SQL'
            SELECT  elo, 
                    view_count, 
                    win_count
            FROM    trainer_pokemon_elo AS tpe 
                JOIN dex AS d 
                    ON tpe.dex_id = d.id AND d.slug = :dex_slug
                JOIN pokemon AS p
                    ON tpe.pokemon_id = p.id
            WHERE   tpe.trainer_external_id = :trainer_external_id
                AND tpe.election_slug = :election_slug
                AND p.slug = :pokemon_slug
            SQL;

        $parameters = [
            'trainer_external_id' => $trainerExternalId,
            'dex_slug' => $dexSlug,
            'election_slug' => $electionSlug,
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

<?php

namespace App\Repository;

use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\GameAvailability;
use App\Entity\Pokedex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Pokedex>
 */
class PokedexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokedex::class);
    }

    /**
     * @return \Traversable<int, array<mixed, mixed>>
     */
    public function getQueryFromDexSlug(string $dexSlug): \Traversable
    {
        $sql = <<<SQL
        SELECT  p.slug AS pokemon_slug, p.name AS pokemon_name, p.french_name AS pokemon_french_name,
                p.icon_name AS pokemon_icon,
                cs.slug AS catch_state_slug, cs.name AS catch_state_name
        FROM    dex_availability AS da
            JOIN pokemon AS p
                ON da.pokemon_id = p.id
            JOIN dex AS d
                ON da.dex_id = d.id
            LEFT JOIN pokedex AS pd
                ON pd.dex_id = da.dex_id AND pd.pokemon_id = da.pokemon_id
            LEFT JOIN catch_state AS cs
                ON pd.catch_state_id = cs.id
        WHERE   d.slug = :dex_slug
        ORDER BY p.national_dex_number, p.family_order
        SQL;

        return $this->getEntityManager()->getConnection()->iterateAssociative(
            $sql,
            [
                'dex_slug' => $dexSlug,
            ]
        );
    }

    public function upsertFromSlugs(string $dexSlug, string $pokemonSlug, string $catchStateSlug): void
    {
        $sql = <<<SQL
        INSERT INTO pokedex (
            id,
            dex_id,
            pokemon_id,
            catch_state_id
        )
        VALUES
        (
            :id,
            (SELECT id FROM dex WHERE slug = :dex_slug),
            (SELECT id FROM pokemon WHERE slug = :pokemon_slug),
            (SELECT id FROM catch_state WHERE slug = :catch_state_slug)
        )
        ON CONFLICT (dex_id, pokemon_id)
        DO
        UPDATE
        SET catch_state_id = excluded.catch_state_id
        SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'id' => Uuid::v4(),
                'dex_slug' => $dexSlug,
                'pokemon_slug' => $pokemonSlug,
                'catch_state_slug' => $catchStateSlug,
            ]
        );
    }
}

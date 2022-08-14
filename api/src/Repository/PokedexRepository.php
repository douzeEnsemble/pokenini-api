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
    public function getListQueryFromDexSlug(string $dexSlug): \Traversable
    {
        $sql = <<<SQL
        SELECT  p.slug AS pokemon_slug, p.name AS pokemon_name, p.french_name AS pokemon_french_name,
                p.icon_name AS pokemon_icon,
                rf.slug as regional_form_slug, rf.name as regional_form_name,
                sf.slug as special_form_slug, sf.name as special_form_name,
                vf.slug as variant_form_slug, vf.name as variant_form_name,
                cs.slug AS catch_state_slug, cs.name AS catch_state_name, cs.french_name AS catch_state_french_name
        FROM    dex_availability AS da
            JOIN pokemon AS p
                ON da.pokemon_id = p.id
            JOIN dex AS d
                ON da.dex_id = d.id
            LEFT JOIN pokedex AS pd
                ON pd.dex_id = da.dex_id AND pd.pokemon_id = da.pokemon_id
            LEFT JOIN catch_state AS cs
                ON pd.catch_state_id = cs.id
            LEFT JOIN regional_form AS rf
                ON p.regional_form_id = rf.id
            LEFT JOIN special_form AS sf
                ON p.special_form_id = sf.id
            LEFT JOIN variant_form AS vf
                ON p.variant_form_id = vf.id
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

    /**
     * @return int[][]|string[][]
     */
    public function getCatchStatesCountsFromDexSlug(string $dexSlug): array
    {
        $sql = <<<SQL
        SELECT  COUNT(pd.id) AS count, cs.slug AS slug, cs.name AS name, cs.french_name AS french_name
        FROM
            catch_state AS cs,
            dex_availability AS da

            JOIN dex AS d
                ON da.dex_id = d.id

            LEFT JOIN pokedex AS pd
                ON pd.dex_id = da.dex_id
                    AND pd.pokemon_id = da.pokemon_id
        WHERE
            d.slug = :dex_slug

            AND (pd.catch_state_id IS NULL OR cs.id = pd.catch_state_id)
        GROUP BY cs.slug, cs.name, cs.french_name, cs.order_number
        ORDER BY cs.order_number
        SQL;

        /** @var int[][]|string[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
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

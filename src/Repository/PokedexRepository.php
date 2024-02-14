<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Entity\Pokedex;
use App\Entity\PokemonAvailabilities;
use App\Repository\Trait\FiltersTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Pokedex>
 */
class PokedexRepository extends ServiceEntityRepository
{
    use FiltersTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokedex::class);
    }

    /**
     * @return \Traversable<int, array<mixed, mixed>>
     */
    public function getListQuery(
        string $trainerExternalId,
        string $dexSlug,
        AlbumFilters $filters,
    ): \Traversable {

        $where = "COALESCE(NULLIF(td.slug, ''), d.slug) = :dex_slug "
            . $this->getFiltersQuery($filters);

        $sql = $this->getListQuerySQL($where);

        $params = array_merge(
            [
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
                'pokemon_availabilities_game_bundle_category'
                    => PokemonAvailabilities::CATEGORY_GAME_BUNDLE,
                'pokemon_availabilities_game_bundle_shiny_category'
                    => PokemonAvailabilities::CATEGORY_GAME_BUNDLE_SHINY,
            ],
            $this->getFiltersParameters($filters),
        );

        $types = array_merge(
            [
                'trainer_external_id' => ParameterType::STRING,
                'dex_slug' => ParameterType::STRING,
                'pokemon_availabilities_game_bundle_category' => ParameterType::STRING,
                'pokemon_availabilities_game_bundle_shiny_category' => ParameterType::STRING,
            ],
            $this->getFiltersTypes(),
        );

        return $this->getEntityManager()->getConnection()->iterateAssociative(
            $sql,
            $params,
            $types,
        );
    }

    /**
     * @return int[][]|string[][]
     */
    public function getCatchStatesCounts(
        string $trainerExternalId,
        string $dexSlug,
        AlbumFilters $filters,
    ): array {

        $where = '1 = 1 ' . $this->getFiltersQuery($filters);

        $sql = <<<SQL
            SELECT  COUNT(pokedex_id) AS count, 
                    cs.slug AS slug, cs.name AS name, cs.french_name AS french_name
            FROM    catch_state AS cs
                    LEFT JOIN (
                    SELECT  pd.id AS pokedex_id, pd.catch_state_id AS catch_state_id
                    FROM
                        dex_availability AS da
                        JOIN dex AS d
                            ON da.dex_id = d.id
                        JOIN pokemon AS p
                            ON da.pokemon_id = p.id
                        LEFT JOIN trainer_dex AS td
                            ON d.id = td.dex_id
                                AND td.trainer_external_id = :trainer_external_id
                        LEFT JOIN pokedex AS pd
                            ON pd.trainer_dex_id = td.id
                                AND pd.pokemon_id = da.pokemon_id
                                AND td.slug = :dex_slug
                        LEFT JOIN category_form AS cf
                            ON p.category_form_id = cf.id
                        LEFT JOIN regional_form AS rf
                            ON p.regional_form_id = rf.id
                        LEFT JOIN special_form AS sf
                            ON p.special_form_id = sf.id
                        LEFT JOIN variant_form AS vf
                            ON p.variant_form_id = vf.id
                        LEFT JOIN "type" AS pt
                            ON p.primary_type_id = pt.id
                        LEFT JOIN "type" AS st
                            ON p.secondary_type_id = st.id
                        LEFT JOIN catch_state AS cs
                            ON pd.catch_state_id = cs.id
                        LEFT JOIN game_bundle AS ogb
                            ON p.original_game_bundle_id = ogb.id
                    WHERE   $where
                ) AS t
                        ON cs.id = t.catch_state_id
            WHERE   cs.deleted_at IS NULL
            GROUP BY cs.slug, cs.name, cs.french_name, cs.order_number
            ORDER BY cs.order_number
            SQL;

        $params = array_merge(
            [
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug
            ],
            $this->getFiltersParameters($filters),
        );

        $types = array_merge(
            [
                'trainer_external_id' => ParameterType::STRING,
                'dex_slug' => ParameterType::STRING,
            ],
            $this->getFiltersTypes(),
        );

        /** @var int[][]|string[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            $params,
            $types,
        );
    }

    public function upsert(
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
        string $catchStateSlug,
    ): void {
        $sql = <<<SQL
            INSERT INTO pokedex (
                id,
                pokemon_id,
                catch_state_id,
                trainer_dex_id
            )
            VALUES
            (
                :id,
                (SELECT id FROM pokemon WHERE slug = :pokemon_slug),
                (SELECT id FROM catch_state WHERE slug = :catch_state_slug),
                (
                    SELECT  td.id
                    FROM    trainer_dex AS td
                    WHERE   td.slug = :dex_slug
                        AND td.trainer_external_id = :trainer_external_id
                )
            )
            ON CONFLICT (pokemon_id, trainer_dex_id)
            DO
            UPDATE
            SET catch_state_id = excluded.catch_state_id
            SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
                'pokemon_slug' => $pokemonSlug,
                'catch_state_slug' => $catchStateSlug,
            ]
        );
    }

    /**
     * @return int[]|string[]
     */
    public function getCatchStateCountsDefinedByTrainer(): array
    {
        $sql = <<<SQL
            SELECT      COUNT(*) AS nb,
                        td.trainer_external_id as trainer
            FROM        pokedex AS p
                JOIN trainer_dex AS td
                    ON p.trainer_dex_id = td.id
            GROUP BY td.trainer_external_id
            ORDER BY nb DESC, td.trainer_external_id ASC
            SQL;

        return $this->getReportsResult($sql);
    }

    /**
     * @return int[]|string[]
     */
    public function getDexUsage(): array
    {
        $sql = <<<SQL
            SELECT      COUNT(DISTINCT td.trainer_external_id) AS nb,
                            d.name, d.french_name
            FROM        dex AS d
                    JOIN trainer_dex AS td ON d.id = td.dex_id
            GROUP BY    d.name, d.french_name, d.order_number
            HAVING      COUNT(DISTINCT td.trainer_external_id) > 0
            ORDER BY    nb DESC, d.order_number
            SQL;

        return $this->getReportsResult($sql);
    }

    /**
     * @return int[]|string[]
     */
    public function getCatchStateUsage(): array
    {
        $sql = <<<SQL
            SELECT      COUNT(*) AS nb,
                        cs.name, cs.french_name, cs.color
            FROM        pokedex AS p
                    LEFT JOIN catch_state AS cs
                        ON p.catch_state_id = cs.id
            GROUP BY    cs.name, cs.french_name, cs.order_number, cs.color
            ORDER BY    cs.order_number
            SQL;

        return $this->getReportsResult($sql);
    }

    /**
     * @return int[]|string[]
     */
    private function getReportsResult(string $sql): array
    {
        /** @var int[]|string[] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }

    private function getListQuerySQL(string $where): string
    {
        return <<<SQL
        SELECT  p.slug AS pokemon_slug,
                p.name AS pokemon_name,
                p.national_dex_number AS pokemon_national_dex_number,
                p.simplified_name AS pokemon_simplified_name,
                p.forms_label AS pokemon_forms_label,
                p.french_name AS pokemon_french_name,
                p.simplified_french_name AS pokemon_simplified_french_name,
                p.forms_french_label AS pokemon_forms_french_label,
                p.icon_name AS pokemon_icon,
                p.family_order AS pokemon_family_order,
                pp.slug AS family_lead_slug,
                cf.slug as category_form_slug,
                cf.name as category_form_name,
                rf.slug as regional_form_slug,
                rf.name as regional_form_name,
                sf.slug as special_form_slug,
                sf.name as special_form_name,
                vf.slug as variant_form_slug,
                vf.name as variant_form_name,
                cs.slug AS catch_state_slug,
                cs.name AS catch_state_name,
                cs.french_name AS catch_state_french_name,
                rdn.dex_number AS pokemon_regional_dex_number,
                pt.slug AS primary_type_slug,
                pt.name AS primary_type_name,
                pt.french_name AS primary_type_french_name,
                st.slug AS secondary_type_slug,
                st.name AS secondary_type_name,
                st.french_name AS secondary_type_french_name,
                ogb.slug AS original_game_bundle_slug,
                pagb.items AS game_bundle_slugs,
                pagbs.items AS game_bundle_shiny_slugs,
                CONCAT(
                    LPAD(CAST(COALESCE(rdn.dex_number, 999) AS varchar), 3, '0'),
                    '-',
                    LPAD(CAST(p.national_dex_number AS varchar), 4, '0'),
                    '-',
                    LPAD(CAST(p.family_order AS varchar), 3, '0')
                ) as pokemon_order_number
        FROM    dex_availability AS da
            JOIN pokemon AS p
                ON da.pokemon_id = p.id
            JOIN dex AS d
                ON da.dex_id = d.id
            LEFT JOIN trainer_dex AS td
                ON d.id = td.dex_id
                    AND td.trainer_external_id = :trainer_external_id
            LEFT JOIN region AS r
                ON d.region_id = r.id
            LEFT JOIN pokedex AS pd
                ON pd.trainer_dex_id = td.id
                AND pd.pokemon_id = da.pokemon_id
            LEFT JOIN catch_state AS cs
                ON pd.catch_state_id = cs.id
            LEFT JOIN category_form AS cf
                ON p.category_form_id = cf.id
            LEFT JOIN regional_form AS rf
                ON p.regional_form_id = rf.id
            LEFT JOIN special_form AS sf
                ON p.special_form_id = sf.id
            LEFT JOIN variant_form AS vf
                ON p.variant_form_id = vf.id
            LEFT JOIN "type" AS pt
                ON p.primary_type_id = pt.id
            LEFT JOIN "type" AS st
                ON p.secondary_type_id = st.id
            LEFT JOIN regional_dex_number AS rdn
                ON r.id IS NOT NULL
                    AND r.id = rdn.region_id
                    AND p.prime_name = rdn.pokemon_name
            LEFT JOIN pokemon AS pp
                ON p.family_id = pp.id
            LEFT JOIN game_bundle AS ogb
                ON p.original_game_bundle_id = ogb.id
            LEFT JOIN pokemon_availabilities AS pagb
                ON p.id = pagb.pokemon_id AND pagb.category = :pokemon_availabilities_game_bundle_category
            LEFT JOIN pokemon_availabilities AS pagbs
                ON p.id = pagbs.pokemon_id AND pagbs.category = :pokemon_availabilities_game_bundle_shiny_category
        WHERE   $where
        ORDER BY pokemon_order_number
        SQL;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\DexQueryOptions;
use App\Entity\Dex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dex>
 */
class DexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dex::class);
    }

    /**
     * @return Query<Dex>
     *
     * @psalm-suppress TooManyTemplateParams
     */
    public function getQueryAll(): Query
    {
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->where($queryBuilder->expr()->isNull('d.deletedAt'));
        $queryBuilder->orderBy('d.orderNumber');

        return $queryBuilder->getQuery();
    }

    /**
     * @return array<array-key, array{slug: string, banner_layers: string}>
     */
    public function getBannerLayers(): array
    {
        $sql = <<<'SQL'
            SELECT      d.slug AS "slug",
                        d.banner_layers AS "banner_layers"
            FROM        dex AS d
            WHERE       d.deleted_at IS NULL
                    AND d.banner_layers IS NOT NULL
            ORDER BY    d.slug ASC
            SQL;

        /** @var array<array-key, array{slug: string, banner_layers: string}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }

    /**
     * @return bool[]|string[]
     */
    public function getData(string $trainerExternalId, string $dexSlug): array
    {
        $sql = <<<'SQL'
            SELECT      COALESCE(td.slug, d.slug) AS "slug",
                        d.slug AS "original_slug",
                        COALESCE(td.name, d.name) AS "name",
                        COALESCE(td.french_name, d.french_name) AS "french_name",
                        d.is_shiny AS "is_shiny",
                        d.is_display_form AS "is_display_form",
                        d.display_template AS "display_template",
                        r.name AS "region_name",
                        r.french_name AS "region_french_name",
                        d.selection_rule AS "selection_rule",
                        COALESCE(td.is_private, true) AS "is_private",
                        COALESCE(td.is_on_home, false) AS "is_on_home",
                        d.description AS "description",
                        d.french_description AS "french_description",
                        TO_CHAR(last_changed_at, 'YYYYMMDD.HHMISS') AS "version",
                        d.is_released AS "is_released",
                        CASE WHEN td.slug <> d.slug THEN true ELSE d.is_premium END AS "is_premium",
                        CASE WHEN td.slug <> d.slug THEN true ELSE false END AS "is_custom"
            FROM        dex AS d
                    LEFT JOIN region AS r
                        ON d.region_id = r.id
                    LEFT JOIN trainer_dex AS td
                        ON td.dex_id = d.id
                        AND td.trainer_external_id = :trainer_external_id
            WHERE      td.slug = :dex_slug
                    OR (td.slug IS NULL AND d.slug = :dex_slug)
            SQL;

        $data = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
            ]
        );

        /** @var bool[]|string[] */
        return $data[0] ?? [];
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getCanHoldElection(DexQueryOptions $options): array
    {
        $where = '';

        if (!$options->includeUnreleasedDex) {
            $where .= ' AND d.is_released = true ';
        }

        if (!$options->includePremiumDex) {
            $where .= ' AND d.is_premium = false ';
        }

        $sql = <<<SQL
                SELECT      d.slug AS "slug",
                            d.slug AS "original_slug",
                            d.name AS "name",
                            d.french_name AS "french_name",
                            d.is_shiny AS "is_shiny",
                            false AS "is_private",
                            false AS "is_on_home",
                            d.is_display_form AS "is_display_form",
                            d.is_released AS "is_released",
                            d.is_premium AS "is_premium",
                            false AS "is_custom",
                            d.description AS "description",
                            d.french_description AS "french_description",
                            COUNT(1) AS dex_total_count
                FROM        dex AS d
                    LEFT JOIN dex_availability AS da
                        ON d.id = da.dex_id
                WHERE       d.can_hold_election = true
                            {$where}
                GROUP BY    d.election_order_number, d.slug, d.name, d.french_name, d.is_shiny, d.is_display_form,
                            d.description, d.french_description, d.is_released, d.is_premium
                ORDER BY    d.election_order_number ASC, d.slug ASC
            SQL;

        /** @var array<array-key, array<string, mixed>> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql, []);
    }
}

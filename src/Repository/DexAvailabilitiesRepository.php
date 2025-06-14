<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Entity\DexAvailability;
use App\Repository\Trait\FiltersTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DexAvailability>
 */
class DexAvailabilitiesRepository extends ServiceEntityRepository
{
    use FiltersTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DexAvailability::class);
    }

    public function removeAll(): void
    {
        $queryBuilder = $this->createQueryBuilder('da')
            ->delete()
        ;

        $queryBuilder->getQuery()->execute();
    }

    public function getTotal(
        string $trainerExternalId,
        string $dexSlug,
        AlbumFilters $filters,
    ): int {
        $where = 'COALESCE(td.slug, d.slug) = :dex_slug '
            .$this->getFiltersQuery($filters);

        $sql = <<<SQL
            SELECT		COUNT(DISTINCT da.pokemon_id)
            FROM		dex_availability AS da
                    JOIN dex AS d
                        ON da.dex_id = d.id
                    LEFT JOIN trainer_dex AS td
                        ON d.id = td.dex_id AND td.trainer_external_id = :trainer_id
                    JOIN pokemon AS p
                        ON da.pokemon_id = p.id
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
                    LEFT JOIN pokedex AS pd
                        ON pd.trainer_dex_id = td.id
                        AND pd.pokemon_id = da.pokemon_id
                    LEFT JOIN catch_state AS cs
                        ON pd.catch_state_id = cs.id
                    LEFT JOIN game_bundle AS ogb
                        ON p.original_game_bundle_id = ogb.id
                    LEFT JOIN pokemon AS pp
                        ON p.family = pp.slug
            WHERE		{$where}
            SQL;

        $dynamicParams = $this->getFiltersParameters($filters);
        $params = array_merge(
            [
                'trainer_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
            ],
            $dynamicParams,
        );

        $types = array_merge(
            [
                'trainer_external_id' => ParameterType::STRING,
                'dex_slug' => ParameterType::STRING,
            ],
            $this->getFiltersTypes(),
        );

        /** @var int */
        return $this->getEntityManager()->getConnection()->fetchOne(
            $sql,
            $params,
            $types,
        );
    }
}

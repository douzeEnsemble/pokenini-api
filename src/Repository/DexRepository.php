<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Dex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
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

    public function getQueryAll(): AbstractQuery
    {
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->orderBy('d.orderNumber');

        return $queryBuilder->getQuery();
    }

    public function countAll(): int
    {
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select($queryBuilder->expr()->count('d'));

        /** @var int */
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return string[]|bool[]
     */
    public function getData(string $trainerExternalId, string $dexSlug): array
    {
        $sql = <<<SQL
        SELECT      d.name AS name, 
                    d.french_name AS french_name,
                    d.is_shiny AS is_shiny,
                    d.is_display_form AS is_display_form,
                    d.display_template AS display_template,
                    d.selection_rule AS selection_rule,
                    COALESCE(td.is_private, d.is_private) AS is_private,
                    COALESCE(td.is_on_home, false) AS is_on_home
        FROM        dex AS d
                LEFT JOIN trainer_dex AS td
                    ON td.dex_id = d.id AND td.trainer_external_id = :trainer_external_id
        WHERE       d.slug = :dex_slug
        SQL;

        $data = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
            ]
        );

        /** @var string[]|bool[] */
        return $data[0] ?? [];
    }
}

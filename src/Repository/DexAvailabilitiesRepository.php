<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DexAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DexAvailability>
 */
class DexAvailabilitiesRepository extends ServiceEntityRepository
{
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

    public function getTotal(string $dexSlug): int
    {
        $sql = <<<SQL
        SELECT		COUNT(DISTINCT da.pokemon_id)
        FROM		dex_availability AS da
                JOIN dex AS d
                    ON da.dex_id = d.id
                JOIN trainer_dex AS td
                    ON d.id = td.dex_id
        WHERE		td.slug = :dex_slug
        SQL;

        /** @var int */
        return $this->getEntityManager()->getConnection()->fetchOne(
            $sql,
            [
                'dex_slug' => $dexSlug,
            ]
        );
    }
}

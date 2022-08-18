<?php

namespace App\Repository;

use App\Entity\DexAvailability;
use App\Entity\GameAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DexAvailability>
 */
class DexAvailabilityRepository extends ServiceEntityRepository
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

    public function getTotalFromDexSlug(string $dexSlug): int
    {
        $queryBuilder = $this->createQueryBuilder('da');
        $queryBuilder->select('count(da)');
        $queryBuilder->join('da.dex', 'd');
        $queryBuilder->where($queryBuilder->expr()->eq('d.slug', ':dex_slug'));
        $queryBuilder->setParameter('dex_slug', $dexSlug);

        /** @var int */
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}

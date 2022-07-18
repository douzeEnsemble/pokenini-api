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
}

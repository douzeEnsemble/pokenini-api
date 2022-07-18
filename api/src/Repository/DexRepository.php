<?php

namespace App\Repository;

use App\Entity\Dex;
use App\Entity\GameAvailability;
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

    public function getQueryAll(): Query
    {
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->orderBy('d.name');

        return $queryBuilder->getQuery();
    }

    public function countAll(): int
    {
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select($queryBuilder->expr()->count('d'));

        /** @var int */
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}

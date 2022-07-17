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
        $qb = $this->createQueryBuilder('d');

        $qb->orderBy('d.name');

        return $qb->getQuery();
    }

    public function countAll(): int
    {
        $qb = $this->createQueryBuilder('d');

        $qb->select($qb->expr()->count('d'));

        /** @var int */
        return $qb->getQuery()->getSingleScalarResult();
    }
}

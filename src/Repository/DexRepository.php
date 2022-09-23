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

    public function getBySlug(string $slug): ?Dex
    {
        $queryBuilder = $this->createQueryBuilder('d');
        $queryBuilder->where($queryBuilder->expr()->eq('d.slug', ':slug'));
        $queryBuilder->setParameter('slug', $slug);

        /** @var Dex|null */
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}

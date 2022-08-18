<?php

namespace App\Repository;

use App\Entity\CatchState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CatchState>
 */
class CatchStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CatchState::class);
    }

    /**
     * @return CatchState[]
     */
    public function getAll(): array
    {
        $queryBuilder = $this->createQueryBuilder('cs');
        $queryBuilder->orderBy('cs.orderNumber');

        /** @var CatchState[] */
        return $queryBuilder->getQuery()->getResult();
    }
}

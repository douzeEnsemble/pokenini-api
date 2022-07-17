<?php

namespace App\Repository;

use App\Entity\GameBundle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameBundle>
 */
class GameBundleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameBundle::class);
    }

    /**
     * @return GameBundle[]
     */
    public function getAll(): array
    {
        $qb = $this->createQueryBuilder('gb');
        $qb->orderBy('gb.name');

        /** @var GameBundle[] */
        return $qb->getQuery()->getResult();
    }
}

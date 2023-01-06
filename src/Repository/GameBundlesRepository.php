<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GameBundle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameBundle>
 */
class GameBundlesRepository extends ServiceEntityRepository
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
        $queryBuilder = $this->createQueryBuilder('gb');
        $queryBuilder->orderBy('gb.name');

        /** @var GameBundle[] */
        return $queryBuilder->getQuery()->getResult();
    }
}

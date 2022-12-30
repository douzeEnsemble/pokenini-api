<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
     * @return string[]
     */
    public function getAllNames(): array
    {
        $queryBuilder = $this->createQueryBuilder('g');
        $queryBuilder->select('g.name');
        $queryBuilder->orderBy('g.orderNumber');

        /** @var string[] */
        return $queryBuilder->getQuery()->getSingleColumnResult();
    }
}

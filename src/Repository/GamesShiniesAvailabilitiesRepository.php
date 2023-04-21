<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GameShinyAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameShinyAvailability>
 */
class GamesShiniesAvailabilitiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameShinyAvailability::class);
    }

    public function removeAll(): void
    {
        $queryBuilder = $this->createQueryBuilder('gsa')
            ->delete()
        ;

        $queryBuilder->getQuery()->execute();
    }
}

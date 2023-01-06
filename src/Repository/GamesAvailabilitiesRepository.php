<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GameAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameAvailability>
 */
class GamesAvailabilitiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameAvailability::class);
    }

    public function removeAll(): void
    {
        $queryBuilder = $this->createQueryBuilder('ga')
            ->delete()
        ;

        $queryBuilder->getQuery()->execute();
    }
}

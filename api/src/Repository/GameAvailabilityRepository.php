<?php

namespace App\Repository;

use App\Entity\GameAvailability;
use App\Entity\GameBundle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GameAvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameAvailability::class);
    }

    public function removeAll(): void
    {
        $qb = $this->createQueryBuilder('ga')
            ->delete()
        ;

        $qb->getQuery()->execute();
    }
}

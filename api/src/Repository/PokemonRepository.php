<?php

namespace App\Repository;

use App\Entity\Pokemon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pokemon>
 */
class PokemonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokemon::class);
    }

    public function removeAll(): void
    {
        $qb = $this->createQueryBuilder('p')
            ->update()
            ->set('p.deletedAt', ':now')
            ->setParameter('now', new \DateTimeImmutable())
        ;

        $qb->getQuery()->execute();
    }

    public function getQueryAll(): Query
    {
        $qb = $this->createQueryBuilder('p');

        $qb->orderBy('p.name');

        return $qb->getQuery();
    }

    public function countAll(): int
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select($qb->expr()->count('p'));

        /** @var int */
        return $qb->getQuery()->getSingleScalarResult();
    }
}

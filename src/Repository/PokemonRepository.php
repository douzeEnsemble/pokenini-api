<?php

declare(strict_types=1);

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
        $queryBuilder = $this->createQueryBuilder('p')
            ->update()
            ->set('p.deletedAt', ':now')
            ->setParameter('now', new \DateTimeImmutable())
        ;

        $queryBuilder->getQuery()->execute();
    }

    public function getQueryAll(): Query
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $queryBuilder->orderBy('p.nationalDexNumber, p.familyOrder');

        return $queryBuilder->getQuery();
    }

    public function countAll(): int
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $queryBuilder->select($queryBuilder->expr()->count('p'));

        /** @var int */
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}

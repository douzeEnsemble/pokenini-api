<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\CollectionsAvailabilities;
use App\Entity\CollectionAvailability;
use App\Entity\Pokemon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CollectionAvailability>
 */
class CollectionsAvailabilitiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CollectionAvailability::class);
    }

    /**
     * @return CollectionsAvailabilities dex slug as property and dex availability as value
     */
    public function getFromPokemon(Pokemon $pokemon): CollectionsAvailabilities
    {
        $queryBuilder = $this->createQueryBuilder('ca');

        $queryBuilder->select('ca.availability');
        $queryBuilder->addSelect('c.slug');

        $queryBuilder->join('ca.collection', 'c');

        $queryBuilder->where($queryBuilder->expr()->eq('ca.pokemonSlug', ':pokemonSlug'));
        $queryBuilder->setParameter('pokemonSlug', $pokemon->slug);

        $queryBuilder->orderBy('c.orderNumber');

        /** @var string[][] $result */
        $result = $queryBuilder->getQuery()->getArrayResult();

        $list = [];
        foreach ($result as $line) {
            $isAvailable = (
                '—' !== $line['availability']
                && '-' !== $line['availability']
                && '' !== $line['availability']
            );

            $list[$line['slug']] = $isAvailable;
        }

        return new CollectionsAvailabilities($list);
    }
}

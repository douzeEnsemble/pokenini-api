<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\GamesAvailabilities;
use App\Entity\GameAvailability;
use App\Entity\Pokemon;
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

    /**
     * @return GamesAvailabilities dex slug as property and dex availability as value
     */
    public function getFromPokemon(Pokemon $pokemon): GamesAvailabilities
    {
        $queryBuilder = $this->createQueryBuilder('ga');

        $queryBuilder->select('ga.availability');
        $queryBuilder->addSelect('g.slug');

        $queryBuilder->join('ga.game', 'g');

        $queryBuilder->where($queryBuilder->expr()->eq('ga.pokemonSlug', ':pokemonSlug'));
        $queryBuilder->setParameter('pokemonSlug', $pokemon->slug);

        $queryBuilder->orderBy('g.name');

        /** @var string[][] $result */
        $result = $queryBuilder->getQuery()->getArrayResult();

        $list = [];
        foreach ($result as $line) {
            /** @var bool $isAvailable */
            $isAvailable = (
                '—' !== $line['availability']
                && '-' !== $line['availability']
                && '' !== $line['availability']
            );

            $list[$line['slug']] = $isAvailable;
        }

        return new GamesAvailabilities($list);
    }
}

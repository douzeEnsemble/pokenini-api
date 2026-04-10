<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\GameBundlesShiniesAvailabilities;
use App\Entity\GameBundleShinyAvailability;
use App\Entity\Pokemon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameBundleShinyAvailability>
 */
class GameBundlesShiniesAvailabilitiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameBundleShinyAvailability::class);
    }

    public function removeAll(): void
    {
        $queryBuilder = $this->createQueryBuilder('gbsa')
            ->delete()
        ;

        $queryBuilder->getQuery()->execute();
    }

    public function calculate(): int
    {
        $sql = <<<'SQL'
            INSERT INTO game_bundle_shiny_availability (id, pokemon_id, bundle_id, is_available)
            SELECT		gen_random_uuid(), pokemon_id, bundle_id, CASE WHEN availability_count > 0 THEN TRUE ELSE FALSE END
            FROM		(
                            SELECT		gb.id as bundle_id, p.id as pokemon_id,
                                        SUM(CASE
                                            WHEN gsa.availability = '—' OR gsa.availability = '-' OR gsa.availability = ''
                                            THEN 0
                                            ELSE 1
                                        END) AS availability_count
                            FROM		game_bundle AS gb
                                    JOIN game AS g
                                        ON gb.id = g.bundle_id
                                    JOIN game_shiny_availability AS gsa
                                        ON g.id = gsa.game_id
                                    JOIN pokemon AS p
                                        ON gsa.pokemon_slug = p.slug
                            GROUP BY	gb.id, p.id
                        ) AS sub
            SQL;

        $result = $this->getEntityManager()->getConnection()->executeQuery($sql);

        /** @var int */
        return $result->rowCount();
    }

    /**
     * @return GameBundlesShiniesAvailabilities dex slug as property and dex availability as value
     */
    public function getFromPokemon(Pokemon $pokemon): GameBundlesShiniesAvailabilities
    {
        $queryBuilder = $this->createQueryBuilder('gbsa');

        $queryBuilder->select('gbsa.isAvailable');
        $queryBuilder->addSelect('b.slug');

        $queryBuilder->join('gbsa.bundle', 'b');

        $queryBuilder->where($queryBuilder->expr()->eq('gbsa.pokemon', ':pokemon'));

        /** @psalm-suppress QueryBuilderSetParameter */
        $queryBuilder->setParameter('pokemon', $pokemon);

        $queryBuilder->orderBy('b.name');

        /** @var string[][] $result */
        $result = $queryBuilder->getQuery()->getArrayResult();

        $list = [];
        foreach ($result as $line) {
            /** @var bool $isAvailable */
            $isAvailable = $line['isAvailable'];

            $list[$line['slug']] = $isAvailable;
        }

        return new GameBundlesShiniesAvailabilities($list);
    }
}

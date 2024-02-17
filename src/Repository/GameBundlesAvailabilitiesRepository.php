<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\GameBundlesAvailabilities;
use App\Entity\GameBundleAvailability;
use App\Entity\Pokemon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameBundleAvailability>
 */
class GameBundlesAvailabilitiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameBundleAvailability::class);
    }

    public function removeAll(): void
    {
        $queryBuilder = $this->createQueryBuilder('gba')
            ->delete()
        ;

        $queryBuilder->getQuery()->execute();
    }

    public function calculate(): int
    {
        $sql = <<<SQL
        INSERT INTO game_bundle_availability (id, pokemon_id, bundle_id, is_available)
        SELECT		gen_random_uuid(), pokemon_id, bundle_id, CASE WHEN availability_count > 0 THEN TRUE ELSE FALSE END
        FROM		(
                        SELECT		gb.id as bundle_id, p.id as pokemon_id,
                                    SUM(CASE
                                        WHEN ga.availability = '—' OR ga.availability = '-' OR ga.availability = ''
                                        THEN 0
                                        ELSE 1
                                    END) AS availability_count
                        FROM		game_bundle AS gb
                                JOIN game AS g
                                    ON gb.id = g.bundle_id
                                JOIN game_availability AS ga
                                    ON g.id = ga.game_id
                                JOIN pokemon AS p
                                    ON ga.pokemon_slug = p.slug
                        GROUP BY	gb.id, p.id
                    ) AS sub
        SQL;

        $result = $this->getEntityManager()->getConnection()->executeQuery($sql);

        return $result->rowCount();
    }

    /**
     * @return GameBundlesAvailabilities dex slug as property and dex availability as value
     */
    public function getFromPokemon(Pokemon $pokemon): GameBundlesAvailabilities
    {
        $queryBuilder = $this->createQueryBuilder('gba');

        $queryBuilder->select('gba.isAvailable');
        $queryBuilder->addSelect('b.slug');

        $queryBuilder->join('gba.bundle', 'b');

        $queryBuilder->where($queryBuilder->expr()->eq('gba.pokemon', ':pokemon'));
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

        return new GameBundlesAvailabilities($list);
    }
}

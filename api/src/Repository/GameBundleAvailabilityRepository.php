<?php

namespace App\Repository;

use App\DTO\GameBundlesAvailabilities;
use App\Entity\GameBundleAvailability;
use App\Entity\Pokemon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameBundleAvailability>
 */
class GameBundleAvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameBundleAvailability::class);
    }

    public function removeAll(): void
    {
        $qb = $this->createQueryBuilder('gba')
            ->delete()
        ;

        $qb->getQuery()->execute();
    }

    public function calculate(): int
    {
        $sql = <<<SQL
        INSERT INTO game_bundle_availability (id, pokemon_id, bundle_id, is_available)
        SELECT		gen_random_uuid(), pokemon, bundle, CASE WHEN availability_count > 0 THEN TRUE ELSE FALSE END
        FROM		(
                        SELECT		gb.id as bundle, p.id as pokemon,
                                    SUM(CASE
                                        WHEN ga.availability = '—' OR ga.availability = ''
                                        THEN 0
                                        ELSE 1
                                    END) AS availability_count
                        FROM		game_bundle AS gb
                                JOIN game AS g
                                    ON gb.id = g.bundle_id
                                JOIN game_availability AS ga
                                    ON g.id = ga.game_id
                                JOIN pokemon AS p
                                    ON ga.pokemon_id = p.id
                        GROUP BY	gb.id, p.id
                    ) AS sub
        SQL;

        $result = $this->getEntityManager()->getConnection()->executeQuery($sql);

        return $result->rowCount();
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return GameBundlesAvailabilities, dex slug as property, dex availability as value
     */
    public function getFromPokemon(Pokemon $pokemon): GameBundlesAvailabilities
    {
        $qb = $this->createQueryBuilder('gba');

        $qb->select('gba.isAvailable');
        $qb->addSelect('b.slug');

        $qb->join('gba.bundle', 'b');

        $qb->where($qb->expr()->eq('gba.pokemon', ':pokemon'));
        $qb->setParameter('pokemon', $pokemon);

        $qb->orderBy('b.name');

        /** @var string[][] $result */
        $result = $qb->getQuery()->getArrayResult();

        $list = [];
        foreach ($result as $line) {
            /** @var bool $isAvailable */
            $isAvailable = $line['isAvailable'];

            $list[$line['slug']] = $isAvailable;
        }

        return new GameBundlesAvailabilities($list);
    }
}

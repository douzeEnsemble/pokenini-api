<?php

namespace App\Repository;

use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\GameAvailability;
use App\Entity\Pokedex;
use App\Entity\Pokemon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pokedex>
 */
class PokedexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokedex::class);
    }

    /**
     * @return \Traversable<int, array<mixed, mixed>>
     */
    public function getQueryFromDexSlug(string $dexSlug): \Traversable
    {
        $sql = <<<SQL
        SELECT  p.slug AS pokemon_slug, p.name AS pokemon_name, p.icon_name AS pokemon_icon,
                cs.slug AS catch_state_slug, cs.name AS catch_state_name
        FROM    dex_availability AS da
            JOIN pokemon AS p
                ON da.pokemon_id = p.id
            JOIN dex AS d
                ON da.dex_id = d.id
            LEFT JOIN pokedex AS pd
                ON pd.dex_id = da.dex_id AND pd.pokemon_id = da.pokemon_id
            LEFT JOIN catch_state AS cs
                ON pd.catch_state_id = cs.id
        WHERE   d.slug = :dex_slug
        ORDER BY p.national_dex_number, p.family_order
        SQL;

        return $this->getEntityManager()->getConnection()->iterateAssociative(
            $sql,
            [
                'dex_slug' => $dexSlug,
            ]
        );
    }
}

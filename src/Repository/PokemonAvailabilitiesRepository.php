<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PokemonAvailabilities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PokemonAvailabilities>
 */
class PokemonAvailabilitiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonAvailabilities::class);
    }

    public function removeAllByCategory(string $category): void
    {
        $queryBuilder = $this->createQueryBuilder('pa');

        $queryBuilder
            ->delete()
            ->where($queryBuilder->expr()->eq('pa.category', ':category'))
        ;

        $queryBuilder->getQuery()->execute([
            'category' => $category,
        ]);
    }

    public function calculateGameBundle(): int
    {
        $sql = <<<'SQL'
            INSERT INTO pokemon_availabilities (id, category, pokemon_id, items)
            SELECT      gen_random_uuid() AS id,
                        :category AS category,
                        gba.pokemon_id AS pokemon_id,
                        string_agg(gb.slug, ',' ORDER BY gb.order_number) AS items
            FROM        game_bundle_availability AS gba
                    JOIN game_bundle AS gb
                        ON gba.bundle_id = gb.id
            WHERE		gba.is_available
            GROUP BY    gba.pokemon_id
            SQL;

        $result = $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'category' => PokemonAvailabilities::CATEGORY_GAME_BUNDLE,
            ]
        );

        /** @var int */
        return $result->rowCount();
    }

    public function calculateGameBundleShiny(): int
    {
        $sql = <<<'SQL'
            INSERT INTO pokemon_availabilities (id, category, pokemon_id, items)
            SELECT      gen_random_uuid() AS id,
                        :category AS category,
                        gbsa.pokemon_id AS pokemon_id,
                        string_agg(gb.slug, ',' ORDER BY gb.order_number) AS items
            FROM        game_bundle_shiny_availability AS gbsa
                    JOIN game_bundle AS gb
                        ON gbsa.bundle_id = gb.id
            WHERE		gbsa.is_available
            GROUP BY    gbsa.pokemon_id
            SQL;

        $result = $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'category' => PokemonAvailabilities::CATEGORY_GAME_BUNDLE_SHINY,
            ]
        );

        /** @var int */
        return $result->rowCount();
    }
}

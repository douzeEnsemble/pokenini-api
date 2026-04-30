<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GameBundle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameBundle>
 */
class GameBundlesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameBundle::class);
    }

    /**
     * @return string[][]
     */
    public function getAll(): array
    {
        $sql = <<<'SQL'
            SELECT      gb.name,
                        gb.french_name as "french_name",
                        gb.slug,
                        gg.slug AS generation_slug
            FROM        game_bundle AS gb
                    JOIN game_generation AS gg
                        ON gb.generation_id = gg.id
            WHERE       gb.deleted_at IS NULL
            ORDER BY    order_number
            SQL;

        /** @var string[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
}

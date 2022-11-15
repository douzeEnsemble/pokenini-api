<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\TrainerDexAttributes;
use App\Entity\TrainerDex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TrainerDex>
 */
class TrainerDexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainerDex::class);
    }

    /**
     * @return \Traversable<int, array<mixed, mixed>>
     */
    public function getListQuery(string $trainerToken): \Traversable
    {
        $sql = <<<SQL
        SELECT  d.name as name,
                d.french_name as french_name,
                d.slug as slug,
                d.is_shiny as is_shiny,
                COALESCE(td.is_private, d.is_private) as is_private,
                COALESCE(td.is_on_home, false) as is_on_home,
                d.is_display_form as is_display_form,
                d.display_template as display_template
        FROM    dex AS d
            LEFT JOIN trainer_dex AS td
                ON td.dex_id = d.id
                AND td.trainer_token = :trainer_token
        ORDER BY d.order_number
        SQL;

        return $this->getEntityManager()->getConnection()->iterateAssociative(
            $sql,
            [
                'trainer_token' => $trainerToken,
            ]
        );
    }

    public function upsert(string $trainerToken, string $dexSlug, TrainerDexAttributes $attributes): void
    {
        $sql = <<<SQL
        INSERT INTO trainer_dex (
            id,
            trainer_token,
            dex_id,
            is_private,
            is_on_home
        )
        VALUES
        (
            :id,
            :trainer_token,
            (SELECT id FROM dex WHERE slug = :dex_slug),
            :is_private,
            :is_on_home
        )
        ON CONFLICT (trainer_token, dex_id)
        DO
        UPDATE
        SET is_private = excluded.is_private,
            is_on_home = excluded.is_on_home
        SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_token' => $trainerToken,
                'dex_slug' => $dexSlug,
                'is_private' => (int) $attributes->isPrivate,
                'is_on_home' => (int) $attributes->isOnHome,
            ]
        );
    }
}

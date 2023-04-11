<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\DexQueryOptions;
use App\DTO\TrainerDexAttributes;
use App\Entity\TrainerDex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
    public function getListQuery(
        string $trainerExternalId,
        DexQueryOptions $options,
    ): \Traversable {

        $where = "";

        if (! $options->includeUnreleasedDex) {
            $where = " AND d.is_released = true ";
        }

        $sql = <<<SQL
        SELECT
                d.slug as dex_slug,
                COALESCE(td.name, d.name) as name,
                COALESCE(td.french_name, d.french_name) as french_name,
                COALESCE(NULLIF(td.slug, ''), d.slug) as slug,
                d.is_shiny as is_shiny,
                COALESCE(td.is_private, d.is_private) as is_private,
                COALESCE(td.is_on_home, false) as is_on_home,
                d.is_display_form as is_display_form,
                d.display_template as display_template,
                d.is_released as is_released
        FROM    dex AS d
            LEFT JOIN trainer_dex AS td
                ON td.dex_id = d.id
                AND td.trainer_external_id = :trainer_external_id
        WHERE   1 = 1
                AND d.deleted_at IS NULL
                $where
        ORDER BY d.order_number, slug
        SQL;

        return $this->getEntityManager()->getConnection()->iterateAssociative(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
            ]
        );
    }

    public function upsert(
        string $trainerExternalId,
        string $dexSlug,
        string $trainerDexSlug,
        TrainerDexAttributes $attributes
    ): void {
        $sql = <<<SQL
        INSERT INTO trainer_dex (
            id,
            trainer_external_id,
            dex_id,
            is_private,
            is_on_home,
            slug
        )
        VALUES
        (
            :id,
            :trainer_external_id,
            (SELECT id FROM dex WHERE slug = :dex_slug),
            :is_private,
            :is_on_home,
            :trainer_dex_slug
        )
        ON CONFLICT (trainer_external_id, dex_id, slug)
        DO
        UPDATE
        SET is_private = excluded.is_private,
            is_on_home = excluded.is_on_home
        SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
                'trainer_dex_slug' => $trainerDexSlug,
                'is_private' => (int) $attributes->isPrivate,
                'is_on_home' => (int) $attributes->isOnHome,
            ]
        );
    }

    public function insertIfNeeded(
        string $trainerExternalId,
        string $dexSlug,
        string $slug = '',
    ): void {
        $sql = <<<SQL
        INSERT INTO trainer_dex (
            id,
            trainer_external_id,
            dex_id,
            slug
        )
        VALUES
        (
            :id,
            :trainer_external_id,
            (SELECT id FROM dex WHERE slug = :dex_slug),
            :slug
        )
        ON CONFLICT (trainer_external_id, dex_id, slug)
        DO NOTHING
        SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
                'slug' => $slug,
            ]
        );
    }
}

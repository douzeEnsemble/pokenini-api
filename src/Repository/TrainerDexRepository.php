<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\DexQueryOptions;
use App\DTO\TrainerDexAttributes;
use App\Entity\TrainerDex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

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
        $where = '';

        if (!$options->includeUnreleasedDex) {
            $where .= ' AND d.is_released = true ';
        }

        if (!$options->includePremiumDex) {
            $where .= ' AND d.is_premium = false ';
        }

        $sql = <<<SQL
            SELECT
                    d.slug as dex_slug,
                    COALESCE(td.name, d.name) as name,
                    COALESCE(td.french_name, d.french_name) as french_name,
                    COALESCE(NULLIF(td.slug, ''), d.slug) as slug,
                    d.is_shiny as is_shiny,
                    COALESCE(td.is_private, true) as is_private,
                    COALESCE(td.is_on_home, false) as is_on_home,
                    d.is_display_form as is_display_form,
                    d.display_template as display_template,
                    d.is_released as is_released,
                    CASE WHEN td.slug <> d.slug THEN true ELSE d.is_premium END AS is_premium,
                    CASE WHEN td.slug <> d.slug THEN true ELSE false END AS is_custom
            FROM    dex AS d
                LEFT JOIN trainer_dex AS td
                    ON td.dex_id = d.id
                    AND td.trainer_external_id = :trainer_external_id
            WHERE   1 = 1
                    AND d.deleted_at IS NULL
                    {$where}
            ORDER BY d.order_number, slug
            SQL;

        return $this->getEntityManager()->getConnection()->iterateAssociative(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
            ]
        );
    }

    public function set(
        string $trainerExternalId,
        string $dexSlug,
        TrainerDexAttributes $attributes
    ): void {
        if (!$this->isCustomized($trainerExternalId, $dexSlug)) {
            $this->upsert($trainerExternalId, $dexSlug, $attributes);

            return;
        }

        $this->updateCustom($trainerExternalId, $dexSlug, $attributes);
    }

    public function insertIfNeeded(
        string $trainerExternalId,
        string $dexSlug,
    ): void {
        $sql = <<<'SQL'
            INSERT INTO trainer_dex (
                id,
                trainer_external_id,
                dex_id,
                name,
                french_name,
                slug
            )
            SELECT
                :id,
                :trainer_external_id,
                d.id,
                d.name,
                d.french_name,
                d.slug
            FROM    dex AS d
            WHERE   d.slug = :dex_slug
            ON CONFLICT (trainer_external_id, slug)
            DO NOTHING
            SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
            ]
        );
    }

    private function isCustomized(
        string $trainerExternalId,
        string $dexSlug
    ): bool {
        $sql = <<<'SQL'
            SELECT      COUNT(*)
            FROM        trainer_dex AS td
                    JOIN dex AS d
                        ON td.dex_id = d.id AND td.slug <> d.slug
            WHERE       td.slug = :dex_slug
                    AND td.trainer_external_id = :trainer_external_id
            SQL;

        $count = $this->getEntityManager()->getConnection()->fetchOne(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
            ]
        );

        return $count > 0;
    }

    private function upsert(
        string $trainerExternalId,
        string $dexSlug,
        TrainerDexAttributes $attributes
    ): void {
        $sql = <<<'SQL'
            INSERT INTO trainer_dex (
                id,
                trainer_external_id,
                dex_id,
                is_private,
                is_on_home,
                name,
                french_name,
                slug
            )
            SELECT
                :id,
                :trainer_external_id,
                d.id,
                :is_private,
                :is_on_home,
                d.name,
                d.french_name,
                d.slug
            FROM    dex AS d
            WHERE   d.slug = :dex_slug
            ON CONFLICT (trainer_external_id, slug)
            DO
            UPDATE
            SET     is_private = excluded.is_private,
                    is_on_home = excluded.is_on_home
            WHERE   trainer_dex.slug = excluded.slug
            SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
                'is_private' => (int) $attributes->isPrivate,
                'is_on_home' => (int) $attributes->isOnHome,
            ]
        );
    }

    private function updateCustom(
        string $trainerExternalId,
        string $dexSlug,
        TrainerDexAttributes $attributes
    ): void {
        $sql = <<<'SQL'
            UPDATE  trainer_dex
            SET     is_private = :is_private,
                    is_on_home = :is_on_home
            WHERE   trainer_dex.slug = :dex_slug
                AND trainer_external_id = :trainer_external_id
            SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
                'is_private' => (int) $attributes->isPrivate,
                'is_on_home' => (int) $attributes->isOnHome,
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TrainerDexLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<TrainerDexLink>
 */
class TrainerDexLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainerDexLink::class);
    }

    /**
     * @return list<array{target_trainer_dex_id: string, target_dex_slug: string}>
     */
    public function getOutgoingEdges(string $trainerExternalId, string $sourceTrainerDexId): array
    {
        $sql = <<<'SQL'
            SELECT      td.id AS target_trainer_dex_id,
                        td.slug AS target_dex_slug
            FROM        trainer_dex_link AS tdl
                    JOIN trainer_dex AS td
                        ON td.id = tdl.target_trainer_dex_id
            WHERE       tdl.trainer_external_id = :trainer_external_id
                    AND tdl.source_trainer_dex_id = :source_trainer_dex_id
            SQL;

        /** @var list<array{target_trainer_dex_id: string, target_dex_slug: string}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
                'source_trainer_dex_id' => $sourceTrainerDexId,
            ],
            [
                'trainer_external_id' => ParameterType::STRING,
                'source_trainer_dex_id' => ParameterType::STRING,
            ],
        );
    }

    /**
     * @return list<array{id: string, pair_id: ?string, direction: string, target_trainer_dex_id: string, target_dex_slug: string, target_name: string, target_french_name: string}>
     */
    public function getForDex(string $trainerExternalId, string $dexSlug): array
    {
        $sql = <<<'SQL'
            SELECT      tdl.id AS id,
                        tdl.pair_id AS pair_id,
                        CASE WHEN tdl.pair_id IS NOT NULL THEN 'both' ELSE 'to' END AS direction,
                        ttd.id AS target_trainer_dex_id,
                        ttd.slug AS target_dex_slug,
                        ttd.name AS target_name,
                        ttd.french_name AS target_french_name
            FROM        trainer_dex_link AS tdl
                    JOIN trainer_dex AS std
                        ON std.id = tdl.source_trainer_dex_id
                    JOIN trainer_dex AS ttd
                        ON ttd.id = tdl.target_trainer_dex_id
            WHERE       tdl.trainer_external_id = :trainer_external_id
                    AND std.slug = :dex_slug

            UNION ALL

            SELECT      tdl.id AS id,
                        tdl.pair_id AS pair_id,
                        'from' AS direction,
                        std.id AS target_trainer_dex_id,
                        std.slug AS target_dex_slug,
                        std.name AS target_name,
                        std.french_name AS target_french_name
            FROM        trainer_dex_link AS tdl
                    JOIN trainer_dex AS std
                        ON std.id = tdl.source_trainer_dex_id
                    JOIN trainer_dex AS ttd
                        ON ttd.id = tdl.target_trainer_dex_id
            WHERE       tdl.trainer_external_id = :trainer_external_id
                    AND ttd.slug = :dex_slug
                    AND tdl.pair_id IS NULL
            SQL;

        /** @var list<array{id: string, pair_id: ?string, direction: string, target_trainer_dex_id: string, target_dex_slug: string, target_name: string, target_french_name: string}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
                'dex_slug' => $dexSlug,
            ],
            [
                'trainer_external_id' => ParameterType::STRING,
                'dex_slug' => ParameterType::STRING,
            ],
        );
    }

    public function exists(string $sourceTrainerDexId, string $targetTrainerDexId): bool
    {
        $sql = <<<'SQL'
            SELECT      COUNT(*)
            FROM        trainer_dex_link
            WHERE       source_trainer_dex_id = :source_trainer_dex_id
                    AND target_trainer_dex_id = :target_trainer_dex_id
            SQL;

        /** @var int $count */
        $count = $this->getEntityManager()->getConnection()->fetchOne(
            $sql,
            [
                'source_trainer_dex_id' => $sourceTrainerDexId,
                'target_trainer_dex_id' => $targetTrainerDexId,
            ],
            [
                'source_trainer_dex_id' => ParameterType::STRING,
                'target_trainer_dex_id' => ParameterType::STRING,
            ],
        );

        return $count > 0;
    }

    public function insert(
        string $trainerExternalId,
        string $sourceTrainerDexId,
        string $targetTrainerDexId,
        ?string $pairId,
    ): void {
        $sql = <<<'SQL'
            INSERT INTO trainer_dex_link (
                id,
                trainer_external_id,
                source_trainer_dex_id,
                target_trainer_dex_id,
                pair_id,
                created_at
            )
            VALUES (
                :id,
                :trainer_external_id,
                :source_trainer_dex_id,
                :target_trainer_dex_id,
                :pair_id,
                :created_at
            )
            SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_external_id' => $trainerExternalId,
                'source_trainer_dex_id' => $sourceTrainerDexId,
                'target_trainer_dex_id' => $targetTrainerDexId,
                'pair_id' => $pairId,
                'created_at' => new \DateTimeImmutable(),
            ],
            [
                'id' => ParameterType::STRING,
                'trainer_external_id' => ParameterType::STRING,
                'source_trainer_dex_id' => ParameterType::STRING,
                'target_trainer_dex_id' => ParameterType::STRING,
                'pair_id' => ParameterType::STRING,
                'created_at' => Types::DATETIME_IMMUTABLE,
            ],
        );
    }

    public function deleteByIdOrPairId(string $trainerExternalId, string $id): void
    {
        $sql = <<<'SQL'
            DELETE FROM trainer_dex_link
            WHERE       trainer_external_id = :trainer_external_id
                    AND (
                        id = :id
                        OR pair_id = (
                            SELECT  inner_link.pair_id
                            FROM    trainer_dex_link AS inner_link
                            WHERE   inner_link.id = :id
                                AND inner_link.trainer_external_id = :trainer_external_id
                        )
                    )
            SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
                'id' => $id,
            ],
            [
                'trainer_external_id' => ParameterType::STRING,
                'id' => ParameterType::STRING,
            ],
        );
    }
}

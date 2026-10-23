<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TrainerPreference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<TrainerPreference>
 */
class TrainerPreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainerPreference::class);
    }

    public function getHomePage(string $trainerExternalId): ?string
    {
        $sql = <<<'SQL'
            SELECT      home_page
            FROM        trainer_preference
            WHERE       trainer_external_id = :trainer_external_id
            SQL;

        /** @var false|string $homePage */
        $homePage = $this->getEntityManager()->getConnection()->fetchOne(
            $sql,
            ['trainer_external_id' => $trainerExternalId],
            ['trainer_external_id' => ParameterType::STRING],
        );

        return false === $homePage ? null : $homePage;
    }

    public function upsertHomePage(string $trainerExternalId, string $homePage): void
    {
        $sql = <<<'SQL'
            INSERT INTO trainer_preference (
                id,
                trainer_external_id,
                home_page,
                updated_at
            )
            VALUES (
                :id,
                :trainer_external_id,
                :home_page,
                :updated_at
            )
            ON CONFLICT (trainer_external_id)
            DO UPDATE SET   home_page = EXCLUDED.home_page,
                            updated_at = EXCLUDED.updated_at
            SQL;

        $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'id' => Uuid::v4(),
                'trainer_external_id' => $trainerExternalId,
                'home_page' => $homePage,
                'updated_at' => new \DateTimeImmutable(),
            ],
            [
                'id' => ParameterType::STRING,
                'trainer_external_id' => ParameterType::STRING,
                'home_page' => ParameterType::STRING,
                'updated_at' => Types::DATETIME_IMMUTABLE,
            ],
        );
    }
}

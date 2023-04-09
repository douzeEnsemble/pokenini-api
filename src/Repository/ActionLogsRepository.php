<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActionLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActionLog>
 */
class ActionLogsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionLog::class);
    }

    /**
     * @return string[][]|null[][]
     */
    public function getLastests(): array
    {
        $sql = <<<SQL
        SELECT
            TRIM(BOTH '_' FROM
                LOWER(
                    regexp_replace(
                        REPLACE("type", 'App\Message\', ''),
                        '([A-Z])',
                        '_\\1',
                        'g'
                    )
                )
            ) as type_action,
            created_at AT TIME ZONE 'UTC' AS created_at,
            done_at AT TIME ZONE 'UTC' AS done_at,
            details
        FROM    (
                SELECT  "type",
                        created_at,
                        done_at,
                        report_data as details,
                        row_number() OVER(
                            PARTITION BY "type"
                            ORDER BY created_at DESC
                        ) AS rn
                FROM    action_log
            ) t
        WHERE t.rn = 1

        ORDER BY    "type" ASC, created_at DESC
        SQL;

        /** @var string[][]|null[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociativeIndexed($sql);
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MessengerAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessengerAction>
 */
class MessengerActionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessengerAction::class);
    }

    /**
     * @return string[]
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
            created_at,
            done_at,
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
                FROM    messenger_action
            ) t
        WHERE t.rn = 1

        ORDER BY    "type" ASC, created_at DESC
        SQL;

        /** @var string[] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociativeIndexed($sql);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\GetterTrait;

use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

trait GetActionLogTrait
{
    protected function getIdToProcess(string $type): string
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        $actionLogId = Uuid::v4();

        $sql = <<<'SQL'
            INSERT INTO action_log (
                id,
                created_at, 
                done_at, 
                report_data, 
                error_trace,
                "type"
            )
            VALUES (
                :id,
                NOW(),
                NULL,
                NULL,
                NULL,
                :type
            )
            SQL;
        $parameters = [
            'id' => $actionLogId,
            'type' => $type,
        ];

        $connection->executeStatement($sql, $parameters);

        return (string) $actionLogId;
    }
}

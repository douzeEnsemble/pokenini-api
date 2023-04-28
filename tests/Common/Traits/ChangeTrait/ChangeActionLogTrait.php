<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\ChangeTrait;

use Doctrine\DBAL\Connection;

trait ChangeActionLogTrait
{
    protected function changeFromFailedIntoToProcess(string $type): void
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        $sql = <<<SQL
        UPDATE  action_log
        SET     done_at = NULL,
                report_data = NULL,
                error_trace = NULL
        WHERE   type = :type
            AND report_data IS NULL
            AND error_trace IS NOT NULL
        SQL;
        $parameters = ['type' => $type];

        $connection->executeQuery($sql, $parameters);
    }
}

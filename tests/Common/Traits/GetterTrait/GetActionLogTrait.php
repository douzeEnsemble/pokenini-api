<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\GetterTrait;

use Doctrine\DBAL\Connection;

trait GetActionLogTrait
{
    /**
     * @return string
     */
    protected function getIdToProcess(string $type): string
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        $sql = <<<SQL
        SELECT  id
        FROM    action_log
        WHERE   type = :type
            AND done_at IS NULL
        SQL;
        $parameters = ['type' => $type];

        /** @var false|string $result */
        $result = $connection->executeQuery($sql, $parameters)->fetchOne();

        if (false === $result) {
            return '';
        }

        return $result;
    }
}

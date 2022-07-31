<?php

namespace App\Tests\Resources\functionnal\CounterTrait;

use Doctrine\DBAL\Connection;

trait CountDexAvailabilityTrait
{
    protected function getDexAvailabilityCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery('SELECT COUNT(*) FROM dex_availability')->fetchOne();
    }
}

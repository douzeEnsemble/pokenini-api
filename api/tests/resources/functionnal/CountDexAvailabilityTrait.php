<?php

namespace App\Tests\resources\functionnal;

use Doctrine\DBAL\Connection;

trait CountDexAvailabilityTrait
{
    protected function getDexAvailabilityCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        return $connection->executeQuery('SELECT COUNT(*) FROM dex_availability')->fetchOne();
    }
}

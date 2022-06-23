<?php

namespace App\Tests\resources\functionnal;

use Doctrine\DBAL\Connection;

trait CountGameAvailabilityTrait
{
    protected function getGameAvailabilityCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        return $connection->executeQuery('SELECT COUNT(*) FROM game_availability')->fetchOne();
    }
}

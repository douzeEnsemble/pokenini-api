<?php

namespace App\Tests\Resources\Traits\CounterTrait;

use Doctrine\DBAL\Connection;

trait CountGameAvailabilityTrait
{
    protected function getGameAvailabilityCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery('SELECT COUNT(*) FROM game_availability')->fetchOne();
    }
}

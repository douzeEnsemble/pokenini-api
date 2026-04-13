<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\CounterTrait;

use Doctrine\DBAL\Connection;

trait CountDexAvailabilityTrait
{
    protected function getDexAvailabilityCount(): int
    {
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery('SELECT COUNT(*) FROM dex_availability')->fetchOne();
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Resources\Traits\CounterTrait;

use Doctrine\DBAL\Connection;

trait CountDexTrait
{
    protected function getDexCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery('SELECT COUNT(*) FROM dex')->fetchOne();
    }
}

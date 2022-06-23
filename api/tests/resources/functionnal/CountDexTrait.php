<?php

namespace App\Tests\resources\functionnal;

use Doctrine\DBAL\Connection;

trait CountDexTrait
{
    protected function getDexCount(): int
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        return $connection->executeQuery('SELECT COUNT(*) FROM dex')->fetchOne();
    }
}

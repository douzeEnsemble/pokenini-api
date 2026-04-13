<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\CounterTrait;

use Doctrine\DBAL\Connection;

trait CountRegionalDexNumberTrait
{
    protected function getRegionalDexNumberCount(): int
    {
        $connection = static::getContainer()->get(Connection::class);

        /** @var int */
        return $connection->executeQuery('SELECT COUNT(*) FROM regional_dex_number')->fetchOne();
    }
}

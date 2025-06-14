<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\HasserTrait;

use Doctrine\DBAL\Connection;

trait HasDexAvailabilityTrait
{
    protected function hasDexAvailability(string $dexName, string $pokemonSlug): bool
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        $sql = <<< 'SQL'
            SELECT  COUNT(*)
            FROM    dex_availability AS da
                JOIN dex AS d
                    ON da.dex_id = d.id
                JOIN pokemon AS p
                    ON da.pokemon_id = p.id
            WHERE   d.name = :dex_name
                AND p.slug = :pokemon_slug
            SQL;

        /** @var int $count */
        $count = $connection->executeQuery(
            $sql,
            [
                'dex_name' => $dexName,
                'pokemon_slug' => $pokemonSlug,
            ]
        )->fetchOne();

        return 0 != $count;
    }
}

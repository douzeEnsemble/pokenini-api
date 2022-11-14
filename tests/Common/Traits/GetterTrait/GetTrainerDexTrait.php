<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\GetterTrait;

use Doctrine\DBAL\Connection;

trait GetTrainerDexTrait
{
    /**
     * @return string[]
     */
    protected function getTrainerDex(string $trainerToken, string $dexSlug): array
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        $sql = <<<'SQL'
        SELECT      td.*
        FROM        trainer_dex AS td
            JOIN dex AS d
                ON td.dex_id = d.id AND d.slug = :dex_slug
        WHERE       td.trainer_token = :trainer_token
        SQL;
        $parameters = [
            'trainer_token' => $trainerToken,
            'dex_slug' => $dexSlug,
        ];

        /** @var false|string[] $result */
        $result = $connection->executeQuery($sql, $parameters)->fetchAssociative();

        if (false === $result) {
            return [];
        }

        return $result;
    }
}

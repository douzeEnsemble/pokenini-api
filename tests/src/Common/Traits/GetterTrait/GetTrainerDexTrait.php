<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\GetterTrait;

use Doctrine\DBAL\Connection;

trait GetTrainerDexTrait
{
    /**
     * @return bool[]|null[]|string[]
     */
    protected function getTrainerDex(string $trainerExternalId, string $dexSlug): array
    {
        $connection = static::getContainer()->get(Connection::class);

        $sql = <<<'SQL'
            SELECT      td.*
            FROM        trainer_dex AS td
                JOIN dex AS d
                    ON td.dex_id = d.id
            WHERE       td.trainer_external_id = :trainer_external_id
                AND     COALESCE(NULLIF(td.slug, ''), d.slug) = :dex_slug
            SQL;
        $parameters = [
            'trainer_external_id' => $trainerExternalId,
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

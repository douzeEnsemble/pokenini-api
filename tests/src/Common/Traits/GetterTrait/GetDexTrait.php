<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\GetterTrait;

use Doctrine\DBAL\Connection;

trait GetDexTrait
{
    /**
     * @return bool[]|null[]|string[]
     */
    protected function getDexFromSlug(string $slug): array
    {
        $connection = static::getContainer()->get(Connection::class);

        $sql = <<<'SQL'
            SELECT      d.*, r.name AS region_name
            FROM        dex AS d
                LEFT JOIN region as r
                    ON d.region_id = r.id
            WHERE       d.slug = :slug
            SQL;

        $parameters = [
            'slug' => $slug,
        ];

        /** @var false|string[] $result */
        $result = $connection->executeQuery($sql, $parameters)->fetchAssociative();

        if (false === $result) {
            return [];
        }

        return $result;
    }
}

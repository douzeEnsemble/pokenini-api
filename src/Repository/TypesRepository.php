<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Type>
 */
class TypesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Type::class);
    }

    /**
     * The name in the requested locale is in "localized_name", falling back to the fallback locale
     * and, as a last resort, to the English name column.
     *
     * @return string[][]
     */
    public function getAll(string $locale = 'en', string $fallbackLocale = 'en'): array
    {
        $sql = <<<'SQL'
            SELECT      name,
                        french_name as "french_name",
                        COALESCE(
                            NULLIF(names ->> :locale, ''),
                            NULLIF(names ->> :fallback_locale, ''),
                            name
                        ) AS "localized_name",
                        slug,
                        color
            FROM        type
            WHERE       deleted_at IS NULL
            ORDER BY    order_number
            SQL;

        /** @var string[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql, [
            'locale' => $locale,
            'fallback_locale' => $fallbackLocale,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CategoryForm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryForm>
 */
class CategoryFormsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryForm::class);
    }

    /**
     * @return string[][]
     */
    public function getAll(string $locale = 'en', string $fallbackLocale = 'en'): array
    {
        $localizedName = LocalizedName::sql();

        $sql = <<<SQL
            SELECT      name,
                        french_name as "french_name",
                        {$localizedName} AS "localized_name",
                        slug
            FROM        category_form
            WHERE       deleted_at IS NULL
            ORDER BY    order_number
            SQL;

        /** @var string[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            LocalizedName::parameters($locale, $fallbackLocale),
        );
    }
}

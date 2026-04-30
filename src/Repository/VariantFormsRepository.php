<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\VariantForm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VariantForm>
 */
class VariantFormsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VariantForm::class);
    }

    /**
     * @return string[][]
     */
    public function getAll(): array
    {
        $sql = <<<'SQL'
            SELECT      name,
                        french_name as "french_name",
                        slug
            FROM        variant_form
            WHERE       deleted_at IS NULL
            ORDER BY    order_number
            SQL;

        /** @var string[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
}

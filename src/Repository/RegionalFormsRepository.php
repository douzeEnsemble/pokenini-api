<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RegionalForm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RegionalForm>
 */
class RegionalFormsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegionalForm::class);
    }

    /**
     * @return string[][]
     */
    public function getAll(): array
    {
        $sql = <<<'SQL'
            SELECT      name,
                        french_name as "frenchName",
                        slug
            FROM        regional_form
            WHERE       deleted_at IS NULL
            ORDER BY    order_number
            SQL;

        /** @var string[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
}

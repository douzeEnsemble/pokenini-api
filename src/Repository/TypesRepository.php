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
     * @return string[][]
     */
    public function getAll(): array
    {
        $sql = <<<SQL
        SELECT      name,
                    french_name as "frenchName",
                    slug,
                    color
        FROM        type
        WHERE       deleted_at IS NULL
        ORDER BY    order_number
        SQL;

        /** @var string[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
}

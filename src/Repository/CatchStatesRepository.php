<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CatchState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CatchState>
 */
class CatchStatesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CatchState::class);
    }

    /**
     * @return string[][]
     */
    public function getAll(): array
    {
        $sql = <<<'SQL'
            SELECT      name,
                        french_name as "frenchName",
                        slug,
                        color
            FROM        catch_state
            WHERE       deleted_at IS NULL
            ORDER BY    order_number
            SQL;

        /** @var string[][] */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
}

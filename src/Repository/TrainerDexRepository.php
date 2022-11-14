<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TrainerDex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TrainerDex>
 */
class TrainerDexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainerDex::class);
    }

    /**
     * @return \Traversable<int, array<mixed, mixed>>
     */
    public function getListQuery(string $trainerToken): \Traversable
    {
        $sql = <<<SQL
        SELECT  d.name as name,
                d.french_name as french_name,
                d.slug as slug,
                d.is_shiny as is_shiny,
                COALESCE(td.is_private, d.is_private) as is_private,
                d.is_display_form as is_display_form,
                d.display_template as display_template
        FROM    dex AS d
            LEFT JOIN trainer_dex AS td
                ON td.dex_id = d.id
                AND td.trainer_token = :trainer_token
        ORDER BY d.order_number
        SQL;

        return $this->getEntityManager()->getConnection()->iterateAssociative(
            $sql,
            [
                'trainer_token' => $trainerToken,
            ]
        );
    }
}

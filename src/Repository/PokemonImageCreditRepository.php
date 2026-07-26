<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PokemonImageCredit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PokemonImageCredit>
 */
class PokemonImageCreditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonImageCredit::class);
    }

    /**
     * @return array<array{source: string}>
     */
    public function findAllDistinctSources(): array
    {
        $sql = <<<'SQL'
            SELECT DISTINCT source
            FROM pokemon_image_credit
            WHERE source IS NOT NULL
            ORDER BY source
            SQL;

        /** @var array<array{source: string}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
}

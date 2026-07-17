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
     * @return array<array{source_name: string, source_url: string}>
     */
    public function findAllDistinctSources(): array
    {
        $sql = <<<'SQL'
            SELECT DISTINCT source_name, source_url
            FROM pokemon_image_credit
            WHERE source_name IS NOT NULL
                AND source_url IS NOT NULL
            ORDER BY source_name
            SQL;

        /** @var array<array{source_name: string, source_url: string}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
}

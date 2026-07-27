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
     * @return array<array{
     *   source: string,
     *   pokemon_slug: string,
     *   pokemon_name: string,
     *   pokemon_french_name: string,
     *   pokemon_icon: string,
     *   size: string,
     *   is_shiny: bool,
     * }>
     */
    public function findAllWithPokemon(): array
    {
        $sql = <<<'SQL'
            SELECT      pic.source AS source,
                        p.slug AS pokemon_slug,
                        p.name AS pokemon_name,
                        p.french_name AS pokemon_french_name,
                        p.icon_name AS pokemon_icon,
                        pic.size AS size,
                        pic.is_shiny AS is_shiny
            FROM        pokemon_image_credit AS pic
                    JOIN pokemon AS p ON p.id = pic.pokemon_id
            WHERE       pic.source IS NOT NULL
            ORDER BY    p.national_dex_number, pic.size, pic.is_shiny
            SQL;

        /** @var array<array{source: string, pokemon_slug: string, pokemon_name: string, pokemon_french_name: string, pokemon_icon: string, size: string, is_shiny: bool}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);
    }
}

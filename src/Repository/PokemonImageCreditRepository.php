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
     * The name in the requested locale is in "localized_name", falling back to the fallback locale
     * and, as a last resort, to the English name column.
     *
     * @return array<array{
     *   pokemon_slug: string,
     *   pokemon_name: string,
     *   pokemon_french_name: string,
     *   localized_name: string,
     *   pokemon_icon: string,
     *   small_regular_credit: ?string,
     *   small_shiny_credit: ?string,
     *   big_regular_credit: ?string,
     *   big_shiny_credit: ?string,
     * }>
     */
    public function findAllPokemonWithCredits(string $locale = 'en', string $fallbackLocale = 'en'): array
    {
        $localizedName = LocalizedName::sql('p');

        $sql = <<<SQL
            SELECT      p.slug AS pokemon_slug,
                        p.name AS pokemon_name,
                        p.french_name AS pokemon_french_name,
                        {$localizedName} AS localized_name,
                        p.icon_name AS pokemon_icon,
                        pic_sr.source AS small_regular_credit,
                        pic_ss.source AS small_shiny_credit,
                        pic_br.source AS big_regular_credit,
                        pic_bs.source AS big_shiny_credit
            FROM        pokemon AS p
                LEFT JOIN pokemon_image_credit AS pic_sr ON p.id = pic_sr.pokemon_id AND pic_sr.size = 'small' AND pic_sr.is_shiny = false
                LEFT JOIN pokemon_image_credit AS pic_ss ON p.id = pic_ss.pokemon_id AND pic_ss.size = 'small' AND pic_ss.is_shiny = true
                LEFT JOIN pokemon_image_credit AS pic_br ON p.id = pic_br.pokemon_id AND pic_br.size = 'big'   AND pic_br.is_shiny = false
                LEFT JOIN pokemon_image_credit AS pic_bs ON p.id = pic_bs.pokemon_id AND pic_bs.size = 'big'   AND pic_bs.is_shiny = true
            WHERE       p.deleted_at IS NULL
            ORDER BY    p.national_dex_number, p.family_order
            SQL;

        /** @var array<array{pokemon_slug: string, pokemon_name: string, pokemon_french_name: string, localized_name: string, pokemon_icon: string, small_regular_credit: ?string, small_shiny_credit: ?string, big_regular_credit: ?string, big_shiny_credit: ?string}> */
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            LocalizedName::parameters($locale, $fallbackLocale),
        );
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['dex_availabilities_list']],
    order: ["dex.orderNumber", "pokemon.nationalDexNumber", "pokemon.familyOrder"]
)]
#[ApiFilter(SearchFilter::class, properties: ['dex.slug' => 'exact'])]
#[ORM\Entity]
/**
 * Will be calculated from Dex configurations
 */
class DexAvailability
{
    use BaseEntityTrait;

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
    #[Groups(["dex_availabilities_list"])]
    public Pokemon $pokemon;

    #[ORM\ManyToOne(targetEntity: Dex::class)]
    public Dex $dex;

    public static function create(
        Pokemon $pokemon,
        Dex $dex
    ): self {
        $dexAvailability = new self();

        $dexAvailability->pokemon = $pokemon;
        $dexAvailability->dex = $dex;

        return $dexAvailability;
    }
}

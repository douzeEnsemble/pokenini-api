<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
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

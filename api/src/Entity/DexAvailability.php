<?php

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
/**
 * Will be calculated from Dex configurations
 */
class DexAvailability
{
    use BaseEntityTrait;

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
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

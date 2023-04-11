<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
/**
 * Will be calculated from Dex configurations
 */
class DexAvailability
{
    use BaseEntityTrait;

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["dex_availabilities_list"])]
    public Pokemon $pokemon;

    #[ORM\ManyToOne(targetEntity: Dex::class)]
    #[ORM\JoinColumn(nullable: false)]
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

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
/**
 * Will be calculated from GameAvailability.
 */
class GameBundleAvailability
{
    use BaseEntityTrait;

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
    #[ORM\JoinColumn(nullable: false)]
    public Pokemon $pokemon;

    #[ORM\ManyToOne(targetEntity: GameBundle::class)]
    #[ORM\JoinColumn(nullable: false)]
    public GameBundle $bundle;

    #[ORM\Column]
    #[Assert\NotNull]
    public bool $isAvailable;

    public static function create(
        Pokemon $pokemon,
        GameBundle $gameBundle,
        bool $isAvailable
    ): self {
        $gameBundleAvailability = new self();

        $gameBundleAvailability->pokemon = $pokemon;
        $gameBundleAvailability->bundle = $gameBundle;
        $gameBundleAvailability->isAvailable = $isAvailable;

        return $gameBundleAvailability;
    }
}

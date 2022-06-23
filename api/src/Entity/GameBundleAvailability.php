<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JetBrains\PhpStorm\Pure;

#[ORM\Entity]
/**
 * Will be calculated from GameAvailability
 */
class GameBundleAvailability
{
    use BaseEntityTrait;

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
    public Pokemon $pokemon;

    #[ORM\ManyToOne(targetEntity: GameBundle::class)]
    public GameBundle $bundle;

    #[ORM\Column]
    #[Assert\NotNull]
    public bool $isAvailable;

    #[Pure]
    public static function create(
        Pokemon $pokemon,
        GameBundle $gameBundle,
        bool $isAvailable
    ): self {
        $gameBundleAvailability = new self();

        $gameBundleAvailability->pokemon = $pokemon;
        $gameBundleAvailability->gameBundle = $gameBundle;
        $gameBundleAvailability->isAvailable = $isAvailable;

        return $gameBundleAvailability;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
/**
 * Will be calculated from GameAvailability
 */
class GameBundleAvailability
{
    use BaseEntityTrait;

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
    #[Groups(["game_bundle_availabilities_list"])]
    public Pokemon $pokemon;

    #[ORM\ManyToOne(targetEntity: GameBundle::class)]
    #[Groups(["game_bundle_availabilities_list"])]
    public GameBundle $bundle;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Groups(["game_bundle_availabilities_list"])]
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

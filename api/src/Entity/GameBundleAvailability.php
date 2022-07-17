<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use JetBrains\PhpStorm\Pure;

#[ApiResource(
    normalizationContext: ['groups' => ['game_bundle_availabilities_list']],
    order: ["pokemon.nationalDexNumber", "pokemon.familyOrder", "gameBundle.orderNumber"]
)]
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

    #[Pure]
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

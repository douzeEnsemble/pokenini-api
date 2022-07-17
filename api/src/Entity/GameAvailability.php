<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    normalizationContext: ['groups' => ['game_availabilities_list']],
    order: ["pokemon.nationalDexNumber", "pokemon.familyOrder", "game.orderNumber"])
]
#[ORM\Entity]
class GameAvailability
{
    use BaseEntityTrait;

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
    #[Groups("game_availabilities_list")]
    public Pokemon $pokemon;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[Groups("game_availabilities_list")]
    public Game $game;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups("game_availabilities_list")]
    public string $availability = '';
}

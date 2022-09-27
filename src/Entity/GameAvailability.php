<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    normalizationContext: ['groups' => ['game_availabilities_list']],
    order: ["game.orderNumber", "pokemonName"]
)]
#[ORM\Entity]
class GameAvailability
{
    use BaseEntityTrait;

    #[ORM\Column]
    #[Groups("game_availabilities_list")]
    public string $pokemonName;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[Groups("game_availabilities_list")]
    public Game $game;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups("game_availabilities_list")]
    public string $availability = '';
}

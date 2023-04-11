<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class GameAvailability
{
    use BaseEntityTrait;

    #[ORM\Column]
    #[Groups("game_availabilities_list")]
    public string $pokemonName;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups("game_availabilities_list")]
    public Game $game;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups("game_availabilities_list")]
    public string $availability = '';
}

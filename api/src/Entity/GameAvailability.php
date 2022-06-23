<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource]
#[ORM\Entity]
class GameAvailability
{
    use BaseEntityTrait;

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
    public Pokemon $pokemon;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    public Game $game;

    #[ORM\Column]
    #[Assert\NotBlank]
    public string $availability = '';
}

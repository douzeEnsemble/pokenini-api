<?php

namespace App\Entity\Traits;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

trait NamedTrait
{
    #[ORM\Column(unique: true)]
    #[Assert\NotBlank]
    #[Groups([
        "pokemon_list",
        "catch_state_list",
        "dex_list",
        "game_list",
        "game_availabilities_list",
        "game_bundle_list",
        "game_generation_list",
        "forms_list",
    ])]
    public string $name = '';

    public function __toString(): string
    {
        return $this->name;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity]
#[UniqueConstraint(name: 'pokemon_dex_trainer', columns: ['pokemon_id', 'dex_id', 'trainer_token'])]
class Pokedex
{
    use BaseEntityTrait;

    #[ORM\ManyToOne]
    public ?Pokemon $pokemon;

    #[ORM\ManyToOne]
    public ?Dex $dex;

    #[ORM\ManyToOne]
    public ?CatchState $catchState;

    #[ORM\Column]
    public string $trainerToken = '';
}

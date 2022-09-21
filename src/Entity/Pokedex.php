<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity]
#[UniqueConstraint(name: 'pokemon_dex', columns: ['pokemon_id', 'dex_id'])]
class Pokedex
{
    use BaseEntityTrait;

    #[ORM\ManyToOne]
    public ?Pokemon $pokemon;

    #[ORM\ManyToOne]
    public ?Dex $dex;

    #[ORM\ManyToOne]
    public ?CatchState $catchState;
}

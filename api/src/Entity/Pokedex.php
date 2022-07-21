<?php

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
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

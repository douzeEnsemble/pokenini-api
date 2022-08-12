<?php

namespace App\Entity\Traits;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

trait FrenchNamedTrait
{
    #[ORM\Column]
    #[Groups([
        "pokemon_list",
        "dex_list",
    ])]
    public string $frenchName = '';
}

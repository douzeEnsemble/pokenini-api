<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity]
class Game
{
    use BaseEntityTrait;
    use NamedTrait;
    use SlugifiedTrait;

    #[ORM\ManyToOne(targetEntity: GameBundle::class)]
    public GameBundle $bundle;
}

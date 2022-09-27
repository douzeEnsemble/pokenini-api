<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\OrderedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(normalizationContext: ['groups' => ['game_bundle_list']], order: ["orderNumber"])]
#[ORM\Entity]
class GameBundle
{
    use BaseEntityTrait;
    use NamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;

    #[ORM\ManyToOne(targetEntity: GameGeneration::class)]
    #[Groups(["pokemon_list", "game_list", "game_bundle_list"])]
    public GameGeneration $generation;
}

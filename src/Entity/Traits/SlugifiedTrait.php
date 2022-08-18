<?php

namespace App\Entity\Traits;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait SlugifiedTrait
{
    public string $name = '';

    #[ORM\Column(unique: true)]
    #[Gedmo\Slug(fields: ['name'], updatable: false, separator: '')]
    #[ApiProperty(identifier: true)]
    #[Groups(["pokemon_list", "dex_list", "game_bundle_list", "catch_state_list"])]
    public string $slug;
}

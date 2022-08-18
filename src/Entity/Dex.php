<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\FrenchNamedTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\OrderedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(normalizationContext: ['groups' => ['dex_list']], order: ["orderNumber"])]
#[ORM\Entity]
class Dex
{
    use BaseEntityTrait;
    use NamedTrait;
    use FrenchNamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;

    #[ORM\Column(length: 1357)]
    public string $selectionRule = '';

    #[ORM\Column]
    #[Groups([
        "dex_list",
    ])]
    public bool $isShiny = false;

    #[ORM\Column]
    #[Groups([
        "dex_list",
    ])]
    public bool $isPrivate = true;

    #[ORM\Column]
    #[Groups([
        "dex_list",
    ])]
    public bool $isDisplayForm = true;
}

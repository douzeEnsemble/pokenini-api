<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\FrenchNamedTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\OrderedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

#[ApiResource(normalizationContext: ['groups' => ['dex_list']], order: ["orderNumber"])]
#[ORM\Entity]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
class Dex
{
    use BaseEntityTrait;
    use NamedTrait;
    use FrenchNamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;
    use SoftDeleteableEntity;

    #[ORM\Column(length: 13570)]
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

    #[ORM\Column]
    #[Groups([
        "dex_list",
    ])]
    public string $displayTemplate = 'box';

    #[ORM\Column(nullable: true)]
    #[Groups([
        "dex_list",
    ])]
    public ?string $regionName = null;
}

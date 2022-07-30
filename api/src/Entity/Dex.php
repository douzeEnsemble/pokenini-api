<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
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
    use SlugifiedTrait;
    use OrderedTrait;

    #[ORM\Column(length: 412)]
    public string $selectionRule = '';
}

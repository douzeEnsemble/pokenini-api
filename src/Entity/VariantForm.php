<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\OrderedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(normalizationContext: ['groups' => ['forms_list']], order: ["orderNumber"])]
#[ORM\Entity]
class VariantForm
{
    use BaseEntityTrait;
    use NamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;
}

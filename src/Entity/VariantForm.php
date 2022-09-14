<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\OrderedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Mapping\Annotation as Gedmo;

#[ApiResource(normalizationContext: ['groups' => ['forms_list']], order: ["orderNumber"])]
#[ORM\Entity]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
class VariantForm
{
    use BaseEntityTrait;
    use NamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;
    use SoftDeleteableEntity;
}

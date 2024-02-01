<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\FrenchNamedTrait;
use App\Entity\Traits\OrderedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
class RegionalForm
{
    use BaseEntityTrait;
    use NamedTrait;
    use FrenchNamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;
    use SoftDeleteableEntity;
}

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity]
class VariantForm
{
    use BaseEntityTrait;
    use NamedTrait;
    use SlugifiedTrait;
}

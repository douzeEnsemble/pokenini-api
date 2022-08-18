<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\FrenchNamedTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\OrderedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(normalizationContext: ['groups' => ['catch_state_list']], order: ["orderNumber"])]
#[ORM\Entity]
class CatchState
{
    use BaseEntityTrait;
    use NamedTrait;
    use FrenchNamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;
}

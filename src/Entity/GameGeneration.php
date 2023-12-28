<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
class GameGeneration
{
    use BaseEntityTrait;
    use NamedTrait;
    use SlugifiedTrait;
    use SoftDeleteableEntity;

    public function getNumber(): int
    {
        return (int) $this->name;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\SlugifiedTrait;
use App\Entity\Traits\SoftDeleteable;
use App\Entity\Traits\SoftDeleteableInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class GameGeneration implements SoftDeleteableInterface
{
    use BaseEntityTrait;
    use NamedTrait;
    use SlugifiedTrait;
    use SoftDeleteable;

    public function getNumber(): int
    {
        return (int) $this->name;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\OrderedTrait;
use App\Entity\Traits\SlugifiedTrait;
use App\Entity\Traits\SoftDeleteable;
use App\Entity\Traits\SoftDeleteableInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class Game implements SoftDeleteableInterface
{
    use BaseEntityTrait;
    use NamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;
    use SoftDeleteable;

    #[ORM\ManyToOne(targetEntity: GameBundle::class)]
    #[ORM\JoinColumn(nullable: false)]
    public GameBundle $bundle;
}

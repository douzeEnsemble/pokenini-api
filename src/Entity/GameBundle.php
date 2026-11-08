<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\FrenchNamedTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\OrderedTrait;
use App\Entity\Traits\SlugifiedTrait;
use App\Entity\Traits\SoftDeleteable;
use App\Entity\Traits\SoftDeleteableInterface;
use App\Entity\Traits\TranslatedNamesTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class GameBundle implements SoftDeleteableInterface
{
    use BaseEntityTrait;
    use NamedTrait;
    use FrenchNamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;
    use SoftDeleteable;
    use TranslatedNamesTrait;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    public GameGeneration $generation;
}

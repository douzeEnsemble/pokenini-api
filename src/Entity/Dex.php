<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\FrenchNamedTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\OrderedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
class Dex
{
    use BaseEntityTrait;
    use NamedTrait;
    use FrenchNamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;
    use SoftDeleteableEntity;

    #[ORM\Column(length: 13570)]
    public string $selectionRule = '';

    #[ORM\Column]
    public bool $isShiny = false;

    #[ORM\Column]
    public bool $isPrivate = true;

    #[ORM\Column]
    public bool $isDisplayForm = true;

    #[ORM\Column(options: ['default' => 'box'])]
    public string $displayTemplate = 'box';

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    public ?Region $region = null;

    #[ORM\Column(length: 655)]
    public string $description = '';

    #[ORM\Column(length: 655)]
    public string $frenchDescription = '';

    #[ORM\Column]
    public bool $isReleased = true;

    #[Gedmo\Timestampable(
        on: 'change',
        field: [
            'selectionRule',
            'isShiny',
            'isDisplayForm',
            'displayTemplate',
            'region'
        ]
    )]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public \DateTime $lastChangedAt;
}

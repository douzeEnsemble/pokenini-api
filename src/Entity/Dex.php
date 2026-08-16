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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class Dex implements SoftDeleteableInterface
{
    use BaseEntityTrait;
    use NamedTrait;
    use FrenchNamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;
    use SoftDeleteable;

    #[ORM\Column(length: 13570)]
    public string $selectionRule = '';

    #[ORM\Column]
    public bool $isShiny = false;

    #[ORM\Column]
    public bool $isPremium = true;

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

    #[ORM\Column(options: ['default' => false])]
    public bool $canHoldElection = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public \DateTime $lastChangedAt;

    #[ORM\Column]
    public int $electionOrderNumber = 0;

    /**
     * @var string[]|null $bannerLayers
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    public ?array $bannerLayers = null;
}

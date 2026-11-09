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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @SuppressWarnings("PHPMD.TooManyFields")
 */
#[ORM\Entity]
final class Dex implements SoftDeleteableInterface
{
    use BaseEntityTrait;
    use NamedTrait;
    use FrenchNamedTrait;
    use SlugifiedTrait;
    use OrderedTrait;
    use SoftDeleteable;
    use TranslatedNamesTrait;

    /** @var array<string, string> the description indexed by locale */
    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true, 'default' => '{}'])]
    public array $descriptions = [];

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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    public ?DexCategory $category = null;

    #[ORM\Column(length: 655)]
    public string $description = '';

    #[ORM\Column(length: 655)]
    public string $frenchDescription = '';

    #[ORM\Column]
    public bool $isReleased = true;

    #[ORM\Column(options: ['default' => false])]
    public bool $canHoldElection = false;

    #[ORM\Column(options: ['default' => false])]
    public bool $canBeColorPlay = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public \DateTime $lastChangedAt;

    #[ORM\Column]
    public int $electionOrderNumber = 0;

    #[ORM\Column(options: ['default' => 0])]
    public int $gameOrderNumber = 0;

    /**
     * @var null|string[] $bannerLayers
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    public ?array $bannerLayers = null;
}

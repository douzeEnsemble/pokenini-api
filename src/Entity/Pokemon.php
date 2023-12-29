<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\FrenchNamedTrait;
use App\Entity\Traits\NamedTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
#[ORM\Entity]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
class Pokemon
{
    use BaseEntityTrait;
    use NamedTrait;
    use FrenchNamedTrait;
    use SoftDeleteableEntity;

    #[ORM\Column(unique: true)]
    public string $slug;

    #[ORM\Column]
    public string $simplifiedName = '';

    #[ORM\Column]
    public string $simplifiedFrenchName = '';

    #[ORM\Column]
    public string $formsLabel = '';

    #[ORM\Column]
    public string $formsFrenchLabel = '';

    #[ORM\Column]
    public int $nationalDexNumber;

    #[ORM\ManyToOne]
    public ?Pokemon $family = null;

    #[ORM\Column]
    public string $primeName;

    #[ORM\Column]
    public bool $bankable = true;

    #[ORM\Column(nullable: true)]
    public ?bool $bankableish = null;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    public GameBundle $originalGameBundle;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    public ?VariantForm $variantForm;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    public ?RegionalForm $regionalForm;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    public ?SpecialForm $specialForm;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    public ?CategoryForm $categoryForm;

    #[ORM\Column]
    public string $iconName;

    #[ORM\Column]
    public int $familyOrder;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    public ?Type $primaryType;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    public ?Type $secondaryType;
}

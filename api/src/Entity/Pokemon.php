<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\SlugifiedTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Mapping\Annotation as Gedmo;

#[ApiResource(order: ["nationalDexNumber", "familyOrder"])]
#[ORM\Entity]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
class Pokemon
{
    use BaseEntityTrait;
    use NamedTrait;
    use SlugifiedTrait;
    use SoftDeleteableEntity;

    #[ORM\Column]
    public int $nationalDexNumber;

    #[ORM\ManyToOne]
    public ?Pokemon $family = null;

    #[ORM\Column]
    public bool $bankable = true;

    #[ORM\Column(nullable: true)]
    public ?bool $bankableish = null;

    #[ORM\ManyToOne]
    public GameBundle $originalGameBundle;

    #[ORM\ManyToOne]
    public ?VariantForm $variantForm;

    #[ORM\ManyToOne]
    public ?RegionalForm $regionalForm;

    #[ORM\ManyToOne]
    public ?SpecialForm $specialForm;

    #[ORM\Column]
    public string $iconName;

    #[ORM\Column]
    public int $familyOrder;
}

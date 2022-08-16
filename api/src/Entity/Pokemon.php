<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\FrenchNamedTrait;
use App\Entity\Traits\NamedTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
#[ApiResource(normalizationContext: ['groups' => ['pokemon_list']], order: ["nationalDexNumber", "familyOrder"])]
#[ORM\Entity]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
class Pokemon
{
    use BaseEntityTrait;
    use NamedTrait;
    use FrenchNamedTrait;
    use SoftDeleteableEntity;

    #[ORM\Column(unique: true)]
    #[ApiProperty(identifier: true)]
    #[Groups(["pokemon_list"])]
    public string $slug;

    #[ORM\Column]
    #[Groups([
        "pokemon_list",
    ])]
    public string $simplifiedName = '';

    #[ORM\Column]
    #[Groups([
        "pokemon_list",
    ])]
    public string $simplifiedFrenchName = '';

    #[ORM\Column]
    #[Groups([
        "pokemon_list",
    ])]
    public string $formsLabel = '';

    #[ORM\Column]
    #[Groups([
        "pokemon_list",
    ])]
    public string $formsFrenchLabel = '';

    #[ORM\Column]
    #[Groups(["pokemon_list"])]
    public int $nationalDexNumber;

    #[ORM\ManyToOne]
    #[Groups(["pokemon_list"])]
    public ?Pokemon $family = null;

    #[ORM\Column]
    #[Groups(["pokemon_list"])]
    public string $primeName;

    #[ORM\Column]
    #[Groups(["pokemon_list"])]
    public bool $bankable = true;

    #[ORM\Column(nullable: true)]
    #[Groups(["pokemon_list"])]
    public ?bool $bankableish = null;

    #[ORM\ManyToOne]
    #[Groups(["pokemon_list"])]
    public GameBundle $originalGameBundle;

    #[ORM\ManyToOne]
    #[Groups(["pokemon_list"])]
    public ?VariantForm $variantForm;

    #[ORM\ManyToOne]
    #[Groups(["pokemon_list"])]
    public ?RegionalForm $regionalForm;

    #[ORM\ManyToOne]
    #[Groups(["pokemon_list"])]
    public ?SpecialForm $specialForm;

    #[ORM\Column]
    #[Groups(["pokemon_list", "dex_availabilities_list"])]
    public string $iconName;

    #[ORM\Column]
    #[Groups(["pokemon_list"])]
    public int $familyOrder;
}

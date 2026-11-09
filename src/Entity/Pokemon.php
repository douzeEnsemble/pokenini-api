<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\FrenchNamedTrait;
use App\Entity\Traits\NamedTrait;
use App\Entity\Traits\SoftDeleteable;
use App\Entity\Traits\SoftDeleteableInterface;
use App\Entity\Traits\TranslatedNamesTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @SuppressWarnings("PHPMD.TooManyFields")
 */
#[ORM\Entity]
final class Pokemon implements SoftDeleteableInterface
{
    use BaseEntityTrait;
    use NamedTrait;
    use FrenchNamedTrait;
    use SoftDeleteable;
    use TranslatedNamesTrait;

    /** @var array<string, string> the simplified name indexed by locale */
    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true, 'default' => '{}'])]
    public array $simplifiedNames = [];

    /** @var array<string, string> the forms label indexed by locale */
    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true, 'default' => '{}'])]
    public array $formsLabels = [];

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

    #[ORM\Column]
    public string $family = '';

    #[ORM\Column]
    public bool $bankable = true;

    #[ORM\Column(nullable: true)]
    public ?bool $bankableish = null;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    public GameBundle $originalGameBundle;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    public ?GameGeneration $originGeneration = null;

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

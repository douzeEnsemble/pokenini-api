<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pokemon_image_credit')]
#[ORM\UniqueConstraint(name: 'uniq_pokemon_image_credit_slot', columns: ['pokemon_id', 'size', 'is_shiny'])]
final class PokemonImageCredit
{
    use BaseEntityTrait;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public Pokemon $pokemon;

    #[ORM\Column(length: 16)]
    public string $size;

    #[ORM\Column]
    public bool $isShiny;

    #[ORM\Column(nullable: true)]
    public ?string $source = null;
}

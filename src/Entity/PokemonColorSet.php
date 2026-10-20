<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * One set of colours of a Pokémon, for the color game.
 * The set at position 1 is the right one, the others are decoys.
 * Colours are stored separated by ";", from the most to the least present.
 */
#[ORM\Entity]
#[ORM\Table(name: 'pokemon_color_set')]
#[ORM\UniqueConstraint(name: 'uniq_pokemon_color_set_slot', columns: ['pokemon_id', 'is_shiny', 'position'])]
final class PokemonColorSet
{
    use BaseEntityTrait;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public Pokemon $pokemon;

    #[ORM\Column]
    public bool $isShiny;

    #[ORM\Column(type: Types::SMALLINT)]
    public int $position;

    #[ORM\Column]
    public string $codes;

    #[ORM\Column]
    public string $names;

    #[ORM\Column]
    public string $frenchNames;
}

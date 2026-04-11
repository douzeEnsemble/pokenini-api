<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['pokemon_id', 'category'])]
final class PokemonAvailabilities
{
    use BaseEntityTrait;

    public const string CATEGORY_GAME_BUNDLE = 'game_bundle';
    public const string CATEGORY_GAME_BUNDLE_SHINY = 'game_bundle_shiny';

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
    #[ORM\JoinColumn(nullable: false)]
    public Pokemon $pokemon;

    #[ORM\Column]
    public string $category;

    /**
     * @var string[] $items
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    public array $items = [];
}

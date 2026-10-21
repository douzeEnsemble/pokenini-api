<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A round of a shiny game.
 * Is shiny tells which image, regular or shiny, is shown to the trainer.
 */
#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uniq_shiny_game_round_position', columns: ['shiny_game_id', 'position'])]
final class ShinyGameRound
{
    use BaseEntityTrait;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ShinyGame $shinyGame;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public Pokemon $pokemon;

    #[ORM\Column]
    public int $position;

    #[ORM\Column]
    public bool $isShiny = false;

    #[ORM\Column(nullable: true)]
    public ?bool $chosenShiny = null;

    #[ORM\Column(nullable: true)]
    public ?bool $isCorrect = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTime $answeredAt = null;
}

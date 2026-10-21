<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A round of a "Which one is shiny?" game.
 * Both images are shown side by side, shiny slot tells which one, 1 or 2, is the shiny one.
 */
#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uniq_which_shiny_game_round_position', columns: ['which_shiny_game_id', 'position'])]
final class WhichShinyGameRound
{
    use BaseEntityTrait;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public WhichShinyGame $whichShinyGame;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public Pokemon $pokemon;

    #[ORM\Column]
    public int $position;

    #[ORM\Column]
    public int $shinySlot = 1;

    #[ORM\Column(nullable: true)]
    public ?int $chosenSlot = null;

    #[ORM\Column(nullable: true)]
    public ?bool $isCorrect = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTime $answeredAt = null;
}

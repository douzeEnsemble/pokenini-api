<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A round of a color game.
 * The choices order holds the colour sets positions, in the order they are displayed.
 */
#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uniq_color_game_round_position', columns: ['color_game_id', 'position'])]
final class ColorGameRound
{
    use BaseEntityTrait;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ColorGame $colorGame;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public Pokemon $pokemon;

    #[ORM\Column]
    public int $position;

    /** Colour sets positions separated by "," */
    #[ORM\Column]
    public string $choicesOrder = '';

    #[ORM\Column(nullable: true)]
    public ?int $chosenChoice = null;

    #[ORM\Column(nullable: true)]
    public ?bool $isCorrect = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTime $answeredAt = null;
}

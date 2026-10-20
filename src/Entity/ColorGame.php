<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(name: 'idx_color_game_trainer', columns: ['trainer_external_id'])]
final class ColorGame
{
    use BaseEntityTrait;

    public const string STATUS_IN_PROGRESS = 'in_progress';
    public const string STATUS_COMPLETED = 'completed';
    public const string STATUS_ABANDONED = 'abandoned';

    #[ORM\Column]
    public string $trainerExternalId;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public Dex $dex;

    #[ORM\Column]
    public bool $isShiny = false;

    #[ORM\Column(length: 16)]
    public string $status = self::STATUS_IN_PROGRESS;

    #[ORM\Column]
    public int $roundCount = 0;

    #[ORM\Column]
    public int $answeredCount = 0;

    #[ORM\Column]
    public int $correctCount = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public \DateTime $startedAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTime $lastPlayedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTime $endedAt = null;
}

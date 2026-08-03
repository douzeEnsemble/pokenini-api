<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity]
#[UniqueConstraint(name: 'trainer_dex_link_edge', columns: ['source_trainer_dex_id', 'target_trainer_dex_id'])]
final class TrainerDexLink
{
    use BaseEntityTrait;

    #[ORM\Column]
    public string $trainerExternalId = '';

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public TrainerDex $sourceTrainerDex;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public TrainerDex $targetTrainerDex;

    #[ORM\Column(nullable: true)]
    public ?string $pairId = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public \DateTimeImmutable $createdAt;
}

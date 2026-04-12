<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use App\Entity\Traits\FrenchNamedTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity]
#[UniqueConstraint(name: 'trainers_dex', columns: ['trainer_external_id', 'slug'])]
final class TrainerDex
{
    use BaseEntityTrait;
    use FrenchNamedTrait;

    #[ORM\Column]
    public string $trainerExternalId = '';

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public Dex $dex;

    #[ORM\Column(options: ['default' => true])]
    public bool $isPrivate = true;

    #[ORM\Column(options: ['default' => false])]
    public bool $isOnHome = false;

    #[ORM\Column]
    public string $name;

    #[ORM\Column]
    public string $slug;
}

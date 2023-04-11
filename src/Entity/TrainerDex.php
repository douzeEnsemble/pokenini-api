<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity]
#[UniqueConstraint(name: 'trainers_dex', columns: ['trainer_external_id', 'dex_id', 'slug'])]
class TrainerDex
{
    use BaseEntityTrait;

    #[ORM\Column]
    public string $trainerExternalId = '';

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public Dex $dex;

    #[ORM\Column(options: ['default' => true])]
    public bool $isPrivate = true;

    #[ORM\Column(options: ['default' => false])]
    public bool $isOnHome = false;

    #[ORM\Column(nullable: true)]
    public ?string $name = null;

    #[ORM\Column(nullable: true)]
    public ?string $frenchName = null;

    #[ORM\Column(options: ['default' => ''])]
    public string $slug = '';
}

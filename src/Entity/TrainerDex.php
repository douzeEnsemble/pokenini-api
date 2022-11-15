<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity]
#[UniqueConstraint(name: 'trainers_dex', columns: ['trainer_token', 'dex_id'])]
class TrainerDex
{
    use BaseEntityTrait;

    #[ORM\Column]
    public string $trainerToken = '';

    #[ORM\ManyToOne]
    public Dex $dex;

    #[ORM\Column]
    public bool $isPrivate = true;

    #[ORM\Column]
    public bool $isOnHome = false;
}

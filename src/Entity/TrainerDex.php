<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\BaseEntityTrait;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity]
class TrainerDex
{
    use BaseEntityTrait;

    #[ORM\Column]
    public string $trainerToken = '';

    #[ORM\ManyToOne]
    public Dex $dex;

    #[ORM\Column]
    public ?bool $isPrivate = true;
}

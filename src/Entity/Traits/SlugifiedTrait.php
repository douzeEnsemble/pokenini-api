<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait SlugifiedTrait
{
    public string $name = '';

    #[ORM\Column(unique: true)]
    public string $slug = '';
}

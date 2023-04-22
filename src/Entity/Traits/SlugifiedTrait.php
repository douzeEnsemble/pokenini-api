<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait SlugifiedTrait
{
    public string $name = '';

    #[ORM\Column(unique: true)]
    #[Gedmo\Slug(fields: ['name'], updatable: false, separator: '')]
    public string $slug;
}

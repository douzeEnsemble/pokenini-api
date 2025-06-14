<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait NamedTrait
{
    #[ORM\Column(unique: true)]
    #[Assert\NotBlank]
    public string $name = '';

    public function __toString(): string
    {
        return $this->name;
    }
}

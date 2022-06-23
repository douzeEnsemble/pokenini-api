<?php

namespace App\Entity\Traits;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
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

<?php

namespace App\Entity\Traits;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

trait OrderedTrait
{
    #[ORM\Column]
    public int $orderNumber = 0;
}

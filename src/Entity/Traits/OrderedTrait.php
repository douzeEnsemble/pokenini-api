<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait OrderedTrait
{
    #[ORM\Column]
    public int $orderNumber = 0;
}

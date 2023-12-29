<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ColoredTrait
{
    #[ORM\Column(options: ['default' => ''])]
    public string $color = '';
}

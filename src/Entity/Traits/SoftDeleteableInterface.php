<?php

declare(strict_types=1);

namespace App\Entity\Traits;

interface SoftDeleteableInterface
{
    public function getDeletedAt(): ?\DateTime;
}

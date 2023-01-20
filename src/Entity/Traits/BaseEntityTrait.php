<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

trait BaseEntityTrait
{
    #[ORM\Id, ORM\Column(name: 'id', type: 'uuid', unique: true), ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $identifier = null;

    public function getIdentifier(): ?Uuid
    {
        return $this->identifier;
    }
}

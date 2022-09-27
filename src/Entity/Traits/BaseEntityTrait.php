<?php

namespace App\Entity\Traits;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

trait BaseEntityTrait
{
    #[ORM\Id, ORM\Column(name: 'id', type: 'uuid', unique: true), ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private ?Uuid $identifier = null;

    public function getIdentifier(): ?Uuid
    {
        return $this->identifier;
    }
}

<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use ApiPlatform\Core\Annotation\ApiProperty;

trait BaseEntityTrait
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true), ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ApiProperty(identifier: false)]
    private ?Uuid $id = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}

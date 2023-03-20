<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
class MessengerAction
{
    use BaseEntityTrait;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?DateTime $doneAt = null;

    #[ORM\Column(nullable: true)]
    public ?string $reportData = null;

    public function __construct(
        #[ORM\Column]
        private string $type,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }
}

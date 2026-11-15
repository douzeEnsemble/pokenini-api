<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class ActionLog
{
    use BaseEntityTrait;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?\DateTimeImmutable $doneAt = null;

    #[ORM\Column(nullable: true)]
    public ?string $reportData = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $errorTrace = null;

    public function __construct(
        #[ORM\Column]
        private string $type,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getType(): string
    {
        return $this->type;
    }
}

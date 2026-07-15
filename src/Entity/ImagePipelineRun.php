<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ImagePipelineRun
{
    use BaseEntityTrait;

    #[ORM\Column(unique: true)]
    public string $correlationId;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public \DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public \DateTime $updatedAt;

    #[ORM\Column(nullable: true)]
    public ?int $workflowARunId = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowAStatus = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowAConclusion = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowAUrl = null;

    #[ORM\Column(nullable: true)]
    public ?int $iconPrNumber = null;

    #[ORM\Column(nullable: true)]
    public ?string $iconPrUrl = null;

    #[ORM\Column(nullable: true)]
    public ?string $iconPrState = null;

    #[ORM\Column(nullable: true)]
    public ?string $iconPrMergeCommitSha = null;

    #[ORM\Column(nullable: true)]
    public ?int $workflowBRunId = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowBStatus = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowBConclusion = null;

    #[ORM\Column(nullable: true)]
    public ?string $workflowBUrl = null;

    #[ORM\Column(nullable: true)]
    public ?int $resourcesPrNumber = null;

    #[ORM\Column(nullable: true)]
    public ?string $resourcesPrUrl = null;

    #[ORM\Column(nullable: true)]
    public ?string $resourcesPrState = null;

    public function __construct(string $correlationId)
    {
        $this->correlationId = $correlationId;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
}

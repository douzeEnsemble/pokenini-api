<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ImagePipelineRunResponse
{
    public function __construct(
        #[SerializedName('correlation_id')]
        public readonly string $correlationId,
        #[SerializedName('workflow_a_run_id')]
        public readonly ?int $workflowARunId,
        #[SerializedName('workflow_a_status')]
        public readonly ?string $workflowAStatus,
        #[SerializedName('workflow_a_conclusion')]
        public readonly ?string $workflowAConclusion,
        #[SerializedName('workflow_a_url')]
        public readonly ?string $workflowAUrl,
        #[SerializedName('icon_pr_number')]
        public readonly ?int $iconPrNumber,
        #[SerializedName('icon_pr_url')]
        public readonly ?string $iconPrUrl,
        #[SerializedName('icon_pr_state')]
        public readonly ?string $iconPrState,
        #[SerializedName('icon_pr_merge_commit_sha')]
        public readonly ?string $iconPrMergeCommitSha,
        #[SerializedName('workflow_b_run_id')]
        public readonly ?int $workflowBRunId,
        #[SerializedName('workflow_b_status')]
        public readonly ?string $workflowBStatus,
        #[SerializedName('workflow_b_conclusion')]
        public readonly ?string $workflowBConclusion,
        #[SerializedName('workflow_b_url')]
        public readonly ?string $workflowBUrl,
        #[SerializedName('resources_pr_number')]
        public readonly ?int $resourcesPrNumber,
        #[SerializedName('resources_pr_url')]
        public readonly ?string $resourcesPrUrl,
        #[SerializedName('resources_pr_state')]
        public readonly ?string $resourcesPrState,
    ) {}
}

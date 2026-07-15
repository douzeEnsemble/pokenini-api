<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ImagePipelineRunResponse;
use App\Entity\ImagePipelineRun;

final class ImagePipelineRunResponseFactory
{
    public static function fromEntity(ImagePipelineRun $run): ImagePipelineRunResponse
    {
        return new ImagePipelineRunResponse(
            correlationId: $run->correlationId,
            workflowARunId: $run->workflowARunId,
            workflowAStatus: $run->workflowAStatus,
            workflowAConclusion: $run->workflowAConclusion,
            workflowAUrl: $run->workflowAUrl,
            iconPrNumber: $run->iconPrNumber,
            iconPrUrl: $run->iconPrUrl,
            iconPrState: $run->iconPrState,
            iconPrMergeCommitSha: $run->iconPrMergeCommitSha,
            workflowBRunId: $run->workflowBRunId,
            workflowBStatus: $run->workflowBStatus,
            workflowBConclusion: $run->workflowBConclusion,
            workflowBUrl: $run->workflowBUrl,
            resourcesPrNumber: $run->resourcesPrNumber,
            resourcesPrUrl: $run->resourcesPrUrl,
            resourcesPrState: $run->resourcesPrState,
        );
    }
}

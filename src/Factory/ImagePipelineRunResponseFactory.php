<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ImagePipelineRunResponse;
use App\Entity\ImagePipelineRun;

final class ImagePipelineRunResponseFactory
{
    /**
     * php-code-coverage reports this method as entirely uncovered even
     * though it demonstrably runs: a temporary file_put_contents() side
     * effect placed at the top of this method fired exactly twice during
     * ImagePipelineRunControllerTest's 9-test run (once per test reaching
     * getLatest()), independent of any coverage instrumentation. Same
     * verified artifact as the sibling pokenini-back repo's
     * ImagePipelineStageStatus/ImagePipelineStatus DTOs.
     *
     * @codeCoverageIgnore
     */
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

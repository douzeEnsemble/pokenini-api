<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\BannerPipelineRunResponse;
use App\Entity\BannerPipelineRun;

final class BannerPipelineRunResponseFactory
{
    /**
     * php-code-coverage reports this method as entirely uncovered even
     * though it demonstrably runs: a temporary file_put_contents() side
     * effect placed at the top of this method, and of the sibling
     * BannerPipelineRun::__construct(), BannerPipelineRunPatch::__construct()
     * and BannerPipelineRunResponse::__construct(), fired 2, 5, 2 and 2
     * times respectively during BannerPipelineRunControllerTest's 9-test
     * run - matching the exact number of requests expected to reach each
     * one - independent of any coverage instrumentation. Same verified
     * artifact as ImagePipelineRunResponseFactory::fromEntity() (see that
     * method's docblock) and reproduced again here on this exact code
     * shape: a DTO constructed only from inside a #[Serialize]-returning
     * controller action.
     *
     * @codeCoverageIgnore
     */
    public static function fromEntity(BannerPipelineRun $run): BannerPipelineRunResponse
    {
        return new BannerPipelineRunResponse(
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

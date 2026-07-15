<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * @SuppressWarnings("PHPMD.ExcessiveParameterList")
 */
final class ImagePipelineRunPatch
{
    /**
     * php-code-coverage reports this constructor as uncovered even though
     * it demonstrably runs on every PATCH request - same verified artifact
     * as ImagePipelineRunResponseFactory::fromEntity(); see that method's
     * docblock for how it was verified.
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        public readonly ?int $workflowARunId = null,
        public readonly ?string $workflowAStatus = null,
        public readonly ?string $workflowAConclusion = null,
        public readonly ?string $workflowAUrl = null,
        public readonly ?int $iconPrNumber = null,
        public readonly ?string $iconPrUrl = null,
        public readonly ?string $iconPrState = null,
        public readonly ?string $iconPrMergeCommitSha = null,
        public readonly ?int $workflowBRunId = null,
        public readonly ?string $workflowBStatus = null,
        public readonly ?string $workflowBConclusion = null,
        public readonly ?string $workflowBUrl = null,
        public readonly ?int $resourcesPrNumber = null,
        public readonly ?string $resourcesPrUrl = null,
        public readonly ?string $resourcesPrState = null,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\BannerPipelineRunController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(BannerPipelineRunController::class)]
final class BannerPipelineRunControllerTest extends AbstractTestControllerApi
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client->disableReboot();
    }

    #[Test]
    public function createThenGetLatest(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/banner-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-1'], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest('GET', '/istration/banner-pipeline-runs/latest');

        $this->assertResponseIsSuccessful();

        /** @var array<string, mixed> $data */
        $data = $this->getJsonDecodedResponseContent();

        $this->assertSame('corr-1', $data['correlation_id']);
        $this->assertNull($data['workflow_a_run_id']);
    }

    #[Test]
    public function createWithDuplicateCorrelationIdConflicts(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/banner-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-dup'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest(
            'POST',
            '/istration/banner-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-dup'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(409);
    }

    #[Test]
    public function createWithMissingCorrelationIdIsBadRequest(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/banner-pipeline-runs',
            [],
            null,
            json_encode(['foo' => 'bar'], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function createWithNonStringCorrelationIdIsBadRequest(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/banner-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 123], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function createWithEmptyBodyIsBadRequest(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/banner-pipeline-runs',
            [],
            null,
            ''
        );

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function getLatestReturns404WhenNoneExist(): void
    {
        $this->apiRequest('GET', '/istration/banner-pipeline-runs/latest');

        $this->assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function patchAppliesFields(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/banner-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-patch'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest(
            'PATCH',
            '/istration/banner-pipeline-runs/corr-patch',
            [],
            null,
            json_encode(['workflowARunId' => 99, 'workflowAStatus' => 'completed'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseIsSuccessful();

        $this->apiRequest('GET', '/istration/banner-pipeline-runs/latest');

        /** @var array<string, mixed> $data */
        $data = $this->getJsonDecodedResponseContent();

        $this->assertSame(99, $data['workflow_a_run_id']);
        $this->assertSame('completed', $data['workflow_a_status']);
    }

    #[Test]
    public function patchReturns404WhenCorrelationIdUnknown(): void
    {
        $this->apiRequest(
            'PATCH',
            '/istration/banner-pipeline-runs/does-not-exist',
            [],
            null,
            json_encode(['workflowARunId' => 1], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function patchWithEmptyBodyIsBadRequest(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/banner-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-empty-patch'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest(
            'PATCH',
            '/istration/banner-pipeline-runs/corr-empty-patch',
            [],
            null,
            ''
        );

        $this->assertResponseStatusCodeSame(400);
    }
}

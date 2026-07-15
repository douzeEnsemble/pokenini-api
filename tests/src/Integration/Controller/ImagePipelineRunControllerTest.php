<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ImagePipelineRunController;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ImagePipelineRunController::class)]
final class ImagePipelineRunControllerTest extends AbstractTestControllerApi
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        // A POST-then-GET (or POST-then-PATCH) within a single test method must
        // see the same uncommitted DB state. KernelBrowser reboots the kernel
        // (and thus the DB connection) between requests by default, which would
        // hide writes made by an earlier request in the same test. Disabling the
        // reboot keeps a single kernel/container/connection for the whole test.
        $this->client->disableReboot();
    }

    public function testCreateThenGetLatest(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-1'], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest('GET', '/istration/image-pipeline-runs/latest');

        $this->assertResponseIsSuccessful();

        $content = $this->getClientResponseContent();
        $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('corr-1', $data['correlation_id']);
        $this->assertNull($data['workflow_a_run_id']);
    }

    public function testCreateWithDuplicateCorrelationIdConflicts(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-dup'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-dup'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(409);
    }

    public function testCreateWithMissingCorrelationIdIsBadRequest(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            json_encode(['foo' => 'bar'], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateWithNonStringCorrelationIdIsBadRequest(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 123], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateWithEmptyBodyIsBadRequest(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            ''
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetLatestReturns404WhenNoneExist(): void
    {
        $this->apiRequest('GET', '/istration/image-pipeline-runs/latest');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testPatchAppliesFields(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-patch'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest(
            'PATCH',
            '/istration/image-pipeline-runs/corr-patch',
            [],
            null,
            json_encode(['workflowARunId' => 99, 'workflowAStatus' => 'completed'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseIsSuccessful();

        $this->apiRequest('GET', '/istration/image-pipeline-runs/latest');
        $data = json_decode($this->getClientResponseContent(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(99, $data['workflow_a_run_id']);
        $this->assertSame('completed', $data['workflow_a_status']);
    }

    public function testPatchReturns404WhenCorrelationIdUnknown(): void
    {
        $this->apiRequest(
            'PATCH',
            '/istration/image-pipeline-runs/does-not-exist',
            [],
            null,
            json_encode(['workflowARunId' => 1], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testPatchWithEmptyBodyIsBadRequest(): void
    {
        $this->apiRequest(
            'POST',
            '/istration/image-pipeline-runs',
            [],
            null,
            json_encode(['correlationId' => 'corr-empty-patch'], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(201);

        $this->apiRequest(
            'PATCH',
            '/istration/image-pipeline-runs/corr-empty-patch',
            [],
            null,
            ''
        );

        $this->assertResponseStatusCodeSame(400);
    }
}

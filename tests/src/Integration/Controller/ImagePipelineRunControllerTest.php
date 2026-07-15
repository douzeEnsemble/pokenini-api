<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ImagePipelineRunController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ImagePipelineRunController::class)]
final class ImagePipelineRunControllerTest extends WebTestCase
{
    protected $client;
    private EntityManagerInterface $entityManager;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        // Ensure clean database for each test
        $this->entityManager->getConnection()->executeStatement('TRUNCATE image_pipeline_run CASCADE');
    }

    protected function apiRequest(
        string $method,
        string $route,
        array $params = [],
        ?array $options = null,
        ?string $content = null,
    ): void {
        $urlParams = \http_build_query($params);

        $this->client->request(
            $method,
            "{$route}?{$urlParams}",
            [],
            [],
            array_merge(
                [
                    'headers' => [
                        'accept' => 'application/json',
                    ],
                ],
                $options ?? ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze']
            ),
            $content
        );
    }

    protected function getClientResponseContent(): string
    {
        return (string) $this->client->getResponse()->getContent();
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
}

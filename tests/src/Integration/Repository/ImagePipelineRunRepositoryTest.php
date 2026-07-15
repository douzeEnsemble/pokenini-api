<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\ImagePipelineRunPatch;
use App\Exception\ImagePipelineRunNotFoundException;
use App\Repository\ImagePipelineRunRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(ImagePipelineRunRepository::class)]
final class ImagePipelineRunRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testCreateThenFindLatest(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $repo->create('corr-1');

        $run = $repo->findLatest();

        $this->assertNotNull($run);
        $this->assertSame('corr-1', $run->correlationId);
        $this->assertNull($run->workflowARunId);
    }

    public function testFindLatestReturnsNullWhenEmpty(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $this->assertNull($repo->findLatest());
    }

    public function testFindLatestReturnsMostRecent(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $repo->create('corr-older');

        // The `created_at` column only stores second-level precision, so two runs
        // created within the same test (a matter of milliseconds apart) could tie.
        // Push the first run's timestamp clearly into the past so this test proves
        // ordering by `createdAt` alone, regardless of any incidental id ordering.
        $older = $repo->findLatest();
        $this->assertNotNull($older);
        $older->createdAt = new \DateTime('-1 hour');
        $entityManager->flush();

        $repo->create('corr-newer');

        $run = $repo->findLatest();

        $this->assertNotNull($run);
        $this->assertSame('corr-newer', $run->correlationId);
    }

    public function testUpdateFieldsAppliesOnlyProvidedFields(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $repo->create('corr-2');

        $repo->updateFields('corr-2', new ImagePipelineRunPatch(
            workflowARunId: 42,
            workflowAStatus: 'completed',
            workflowAConclusion: 'success',
            workflowAUrl: 'https://github.com/douzeEnsemble/pokenini-icon/actions/runs/42',
        ));

        $run = $repo->findLatest();

        $this->assertNotNull($run);
        $this->assertSame(42, $run->workflowARunId);
        $this->assertSame('completed', $run->workflowAStatus);
        $this->assertSame('success', $run->workflowAConclusion);
        $this->assertSame('https://github.com/douzeEnsemble/pokenini-icon/actions/runs/42', $run->workflowAUrl);
        $this->assertNull($run->iconPrNumber);
    }

    public function testUpdateFieldsPreservesFieldsFromAnEarlierPatch(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $repo->create('corr-3');

        $repo->updateFields('corr-3', new ImagePipelineRunPatch(workflowARunId: 1));
        $repo->updateFields('corr-3', new ImagePipelineRunPatch(iconPrNumber: 2));

        $run = $repo->findLatest();

        $this->assertNotNull($run);
        $this->assertSame(1, $run->workflowARunId);
        $this->assertSame(2, $run->iconPrNumber);
    }

    public function testUpdateFieldsAppliesAllProvidedFields(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $repo->create('corr-4');

        $repo->updateFields('corr-4', new ImagePipelineRunPatch(
            workflowARunId: 1,
            workflowAStatus: 'completed',
            workflowAConclusion: 'success',
            workflowAUrl: 'https://example.test/workflow-a',
            iconPrNumber: 2,
            iconPrUrl: 'https://example.test/icon-pr',
            iconPrState: 'open',
            iconPrMergeCommitSha: 'abc123',
            workflowBRunId: 3,
            workflowBStatus: 'completed',
            workflowBConclusion: 'success',
            workflowBUrl: 'https://example.test/workflow-b',
            resourcesPrNumber: 4,
            resourcesPrUrl: 'https://example.test/resources-pr',
            resourcesPrState: 'merged',
        ));

        $run = $repo->findLatest();

        $this->assertNotNull($run);
        $this->assertSame(1, $run->workflowARunId);
        $this->assertSame('completed', $run->workflowAStatus);
        $this->assertSame('success', $run->workflowAConclusion);
        $this->assertSame('https://example.test/workflow-a', $run->workflowAUrl);
        $this->assertSame(2, $run->iconPrNumber);
        $this->assertSame('https://example.test/icon-pr', $run->iconPrUrl);
        $this->assertSame('open', $run->iconPrState);
        $this->assertSame('abc123', $run->iconPrMergeCommitSha);
        $this->assertSame(3, $run->workflowBRunId);
        $this->assertSame('completed', $run->workflowBStatus);
        $this->assertSame('success', $run->workflowBConclusion);
        $this->assertSame('https://example.test/workflow-b', $run->workflowBUrl);
        $this->assertSame(4, $run->resourcesPrNumber);
        $this->assertSame('https://example.test/resources-pr', $run->resourcesPrUrl);
        $this->assertSame('merged', $run->resourcesPrState);
    }

    public function testUpdateFieldsThrowsWhenCorrelationIdUnknown(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $this->expectException(ImagePipelineRunNotFoundException::class);

        $repo->updateFields('does-not-exist', new ImagePipelineRunPatch(workflowARunId: 1));
    }
}

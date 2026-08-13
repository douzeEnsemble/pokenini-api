<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\BannerPipelineRunPatch;
use App\Repository\BannerPipelineRunRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(BannerPipelineRunRepository::class)]
final class BannerPipelineRunRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function createThenFindLatest(): void
    {
        $repo = self::getContainer()->get(BannerPipelineRunRepository::class);

        $repo->create('corr-1');

        $run = $repo->findLatest();

        $this->assertNotNull($run);
        $this->assertSame('corr-1', $run->correlationId);
        $this->assertNull($run->workflowARunId);
    }

    #[Test]
    public function findLatestReturnsNullWhenEmpty(): void
    {
        $repo = self::getContainer()->get(BannerPipelineRunRepository::class);

        $this->assertNull($repo->findLatest());
    }

    #[Test]
    public function findLatestReturnsMostRecent(): void
    {
        $repo = self::getContainer()->get(BannerPipelineRunRepository::class);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $repo->create('corr-older');

        $older = $repo->findLatest();
        $this->assertNotNull($older);
        $older->createdAt = new \DateTime('-1 hour');
        $entityManager->flush();

        $repo->create('corr-newer');

        $run = $repo->findLatest();

        $this->assertNotNull($run);
        $this->assertSame('corr-newer', $run->correlationId);
    }

    #[Test]
    public function updateFieldsAppliesOnlyProvidedFields(): void
    {
        $repo = self::getContainer()->get(BannerPipelineRunRepository::class);

        $repo->create('corr-2');

        $repo->updateFields('corr-2', new BannerPipelineRunPatch(
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

    #[Test]
    public function updateFieldsPreservesFieldsFromAnEarlierPatch(): void
    {
        $repo = self::getContainer()->get(BannerPipelineRunRepository::class);

        $repo->create('corr-3');

        $repo->updateFields('corr-3', new BannerPipelineRunPatch(workflowARunId: 1));
        $repo->updateFields('corr-3', new BannerPipelineRunPatch(iconPrNumber: 2));

        $run = $repo->findLatest();

        $this->assertNotNull($run);
        $this->assertSame(1, $run->workflowARunId);
        $this->assertSame(2, $run->iconPrNumber);
    }

    #[Test]
    public function updateFieldsAppliesAllProvidedFields(): void
    {
        $repo = self::getContainer()->get(BannerPipelineRunRepository::class);

        $repo->create('corr-4');

        $repo->updateFields('corr-4', new BannerPipelineRunPatch(
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

    #[Test]
    public function updateFieldsReturnsFalseWhenCorrelationIdUnknown(): void
    {
        $repo = self::getContainer()->get(BannerPipelineRunRepository::class);

        $this->assertFalse($repo->updateFields('does-not-exist', new BannerPipelineRunPatch(workflowARunId: 1)));
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\ImagePipelineRunPatch;
use App\Exception\ImagePipelineRunNotFoundException;
use App\Repository\ImagePipelineRunRepository;
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

        $repo->create('corr-older');
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
        $this->assertNull($run->iconPrNumber);
    }

    public function testUpdateFieldsThrowsWhenCorrelationIdUnknown(): void
    {
        $repo = self::getContainer()->get(ImagePipelineRunRepository::class);

        $this->expectException(ImagePipelineRunNotFoundException::class);

        $repo->updateFields('does-not-exist', new ImagePipelineRunPatch(workflowARunId: 1));
    }
}

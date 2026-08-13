<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\BannerPipelineRunPatch;
use App\Entity\BannerPipelineRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BannerPipelineRun>
 */
class BannerPipelineRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BannerPipelineRun::class);
    }

    public function create(string $correlationId): void
    {
        $run = new BannerPipelineRun($correlationId);

        $this->getEntityManager()->persist($run);
        $this->getEntityManager()->flush();
    }

    /**
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     * @SuppressWarnings("PHPMD.NPathComplexity")
     */
    public function updateFields(string $correlationId, BannerPipelineRunPatch $patch): bool
    {
        $run = $this->findOneBy(['correlationId' => $correlationId]);

        if (null === $run) {
            return false;
        }

        if (null !== $patch->workflowARunId) {
            $run->workflowARunId = $patch->workflowARunId;
        }

        if (null !== $patch->workflowAStatus) {
            $run->workflowAStatus = $patch->workflowAStatus;
        }

        if (null !== $patch->workflowAConclusion) {
            $run->workflowAConclusion = $patch->workflowAConclusion;
        }

        if (null !== $patch->workflowAUrl) {
            $run->workflowAUrl = $patch->workflowAUrl;
        }

        if (null !== $patch->iconPrNumber) {
            $run->iconPrNumber = $patch->iconPrNumber;
        }

        if (null !== $patch->iconPrUrl) {
            $run->iconPrUrl = $patch->iconPrUrl;
        }

        if (null !== $patch->iconPrState) {
            $run->iconPrState = $patch->iconPrState;
        }

        if (null !== $patch->iconPrMergeCommitSha) {
            $run->iconPrMergeCommitSha = $patch->iconPrMergeCommitSha;
        }

        if (null !== $patch->workflowBRunId) {
            $run->workflowBRunId = $patch->workflowBRunId;
        }

        if (null !== $patch->workflowBStatus) {
            $run->workflowBStatus = $patch->workflowBStatus;
        }

        if (null !== $patch->workflowBConclusion) {
            $run->workflowBConclusion = $patch->workflowBConclusion;
        }

        if (null !== $patch->workflowBUrl) {
            $run->workflowBUrl = $patch->workflowBUrl;
        }

        if (null !== $patch->resourcesPrNumber) {
            $run->resourcesPrNumber = $patch->resourcesPrNumber;
        }

        if (null !== $patch->resourcesPrUrl) {
            $run->resourcesPrUrl = $patch->resourcesPrUrl;
        }

        if (null !== $patch->resourcesPrState) {
            $run->resourcesPrState = $patch->resourcesPrState;
        }

        $run->updatedAt = new \DateTime();

        $this->getEntityManager()->flush();

        return true;
    }

    public function findLatest(): ?BannerPipelineRun
    {
        /** @var ?BannerPipelineRun */
        return $this->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ImagePipelineRunPatch;
use App\DTO\Response\ImagePipelineRunResponse;
use App\Factory\ImagePipelineRunResponseFactory;
use App\Service\ImagePipelineRunService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/istration/image-pipeline-runs')]
final class ImagePipelineRunController extends AbstractController
{
    public function __construct(
        private readonly ImagePipelineRunService $service,
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $data = $this->decodeBody($request);

        if (!isset($data['correlationId']) || !\is_string($data['correlationId'])) {
            throw new BadRequestHttpException();
        }

        try {
            $this->service->create($data['correlationId']);
        } catch (UniqueConstraintViolationException $e) {
            throw new ConflictHttpException(previous: $e);
        }

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route('/{correlationId}', methods: ['PATCH'])]
    public function patch(string $correlationId, Request $request): Response
    {
        $data = $this->decodeBody($request);

        $patch = new ImagePipelineRunPatch(
            workflowARunId: isset($data['workflowARunId']) ? (int) $data['workflowARunId'] : null,
            workflowAStatus: isset($data['workflowAStatus']) ? (string) $data['workflowAStatus'] : null,
            workflowAConclusion: isset($data['workflowAConclusion']) ? (string) $data['workflowAConclusion'] : null,
            workflowAUrl: isset($data['workflowAUrl']) ? (string) $data['workflowAUrl'] : null,
            iconPrNumber: isset($data['iconPrNumber']) ? (int) $data['iconPrNumber'] : null,
            iconPrUrl: isset($data['iconPrUrl']) ? (string) $data['iconPrUrl'] : null,
            iconPrState: isset($data['iconPrState']) ? (string) $data['iconPrState'] : null,
            iconPrMergeCommitSha: isset($data['iconPrMergeCommitSha']) ? (string) $data['iconPrMergeCommitSha'] : null,
            workflowBRunId: isset($data['workflowBRunId']) ? (int) $data['workflowBRunId'] : null,
            workflowBStatus: isset($data['workflowBStatus']) ? (string) $data['workflowBStatus'] : null,
            workflowBConclusion: isset($data['workflowBConclusion']) ? (string) $data['workflowBConclusion'] : null,
            workflowBUrl: isset($data['workflowBUrl']) ? (string) $data['workflowBUrl'] : null,
            resourcesPrNumber: isset($data['resourcesPrNumber']) ? (int) $data['resourcesPrNumber'] : null,
            resourcesPrUrl: isset($data['resourcesPrUrl']) ? (string) $data['resourcesPrUrl'] : null,
            resourcesPrState: isset($data['resourcesPrState']) ? (string) $data['resourcesPrState'] : null,
        );

        $updated = $this->service->updateFields($correlationId, $patch);

        if (!$updated) {
            throw new NotFoundHttpException();
        }

        return new Response();
    }

    #[Route('/latest', methods: ['GET'])]
    #[Serialize]
    public function getLatest(): ImagePipelineRunResponse
    {
        $run = $this->service->findLatest();

        if (null === $run) {
            throw new NotFoundHttpException();
        }

        return ImagePipelineRunResponseFactory::fromEntity($run);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeBody(Request $request): array
    {
        $content = $request->getContent();

        if (!$content) {
            throw new BadRequestHttpException();
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\BannerPipelineRunPatch;
use App\DTO\Response\BannerPipelineRunResponse;
use App\Factory\BannerPipelineRunResponseFactory;
use App\Service\BannerPipelineRunService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/istration/banner-pipeline-runs')]
final class BannerPipelineRunController extends AbstractController
{
    public function __construct(
        private readonly BannerPipelineRunService $service,
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

        $patch = new BannerPipelineRunPatch(
            workflowARunId: self::intOrNull($data, 'workflowARunId'),
            workflowAStatus: self::stringOrNull($data, 'workflowAStatus'),
            workflowAConclusion: self::stringOrNull($data, 'workflowAConclusion'),
            workflowAUrl: self::stringOrNull($data, 'workflowAUrl'),
            iconPrNumber: self::intOrNull($data, 'iconPrNumber'),
            iconPrUrl: self::stringOrNull($data, 'iconPrUrl'),
            iconPrState: self::stringOrNull($data, 'iconPrState'),
            iconPrMergeCommitSha: self::stringOrNull($data, 'iconPrMergeCommitSha'),
            workflowBRunId: self::intOrNull($data, 'workflowBRunId'),
            workflowBStatus: self::stringOrNull($data, 'workflowBStatus'),
            workflowBConclusion: self::stringOrNull($data, 'workflowBConclusion'),
            workflowBUrl: self::stringOrNull($data, 'workflowBUrl'),
            resourcesPrNumber: self::intOrNull($data, 'resourcesPrNumber'),
            resourcesPrUrl: self::stringOrNull($data, 'resourcesPrUrl'),
            resourcesPrState: self::stringOrNull($data, 'resourcesPrState'),
        );

        $updated = $this->service->updateFields($correlationId, $patch);

        if (!$updated) {
            throw new NotFoundHttpException();
        }

        return new Response();
    }

    #[Route('/latest', methods: ['GET'])]
    #[Serialize]
    public function getLatest(): BannerPipelineRunResponse
    {
        $run = $this->service->findLatest();

        if (null === $run) {
            throw new NotFoundHttpException();
        }

        return BannerPipelineRunResponseFactory::fromEntity($run);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeBody(Request $request): array
    {
        $content = $request->getContent();

        if ('' === $content) {
            throw new BadRequestHttpException();
        }

        /** @var array<string, mixed> */
        return json_decode($content, true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function intOrNull(array $data, string $key): ?int
    {
        if (!isset($data[$key]) || !\is_scalar($data[$key])) {
            return null;
        }

        return (int) $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function stringOrNull(array $data, string $key): ?string
    {
        if (!isset($data[$key]) || !\is_scalar($data[$key])) {
            return null;
        }

        return (string) $data[$key];
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\TrainerDexLinkResponse;
use App\Exception\DuplicateTrainerDexLinkException;
use App\Exception\SelfTrainerDexLinkException;
use App\Exception\TrainerDexNotFoundException;
use App\Factory\TrainerDexLinkResponseFactory;
use App\Service\TrainerDexLinkService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/trainer_dex_link')]
final class TrainerDexLinkController extends AbstractController
{
    public function __construct(
        private readonly TrainerDexLinkService $service,
    ) {}

    /** @return TrainerDexLinkResponse[] */
    #[Route(path: '/{trainerExternalId}/{dexSlug}', methods: ['GET'])]
    #[Serialize]
    public function list(string $trainerExternalId, string $dexSlug): array
    {
        $rows = $this->service->listForDex($trainerExternalId, $dexSlug);

        return TrainerDexLinkResponseFactory::fromSqlRows($rows);
    }

    #[Route(path: '/{trainerExternalId}', methods: ['POST'])]
    public function create(string $trainerExternalId, Request $request): Response
    {
        $json = $request->getContent();

        if (!$json) {
            throw new BadRequestHttpException();
        }

        /** @var array{sourceDexSlug?: mixed, targetDexSlug?: mixed, bidirectional?: mixed} $content */
        $content = json_decode($json, true) ?? [];

        if (!isset($content['sourceDexSlug'], $content['targetDexSlug'])
            || !\is_string($content['sourceDexSlug'])
            || !\is_string($content['targetDexSlug'])
        ) {
            throw new BadRequestHttpException();
        }

        $bidirectional = $content['bidirectional'] ?? false;

        if (!\is_bool($bidirectional)) {
            throw new BadRequestHttpException();
        }

        try {
            $this->service->create(
                $trainerExternalId,
                $content['sourceDexSlug'],
                $content['targetDexSlug'],
                $bidirectional,
            );
        } catch (SelfTrainerDexLinkException $e) {
            throw new BadRequestHttpException(previous: $e);
        } catch (TrainerDexNotFoundException $e) {
            throw new NotFoundHttpException(previous: $e);
        } catch (DuplicateTrainerDexLinkException $e) {
            throw new ConflictHttpException(previous: $e);
        }

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/{trainerExternalId}/{linkId}', methods: ['DELETE'])]
    public function delete(string $trainerExternalId, string $linkId): Response
    {
        $this->service->delete($trainerExternalId, $linkId);

        return new Response();
    }
}

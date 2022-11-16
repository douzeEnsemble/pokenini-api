<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TrainerDexAttributes;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\TrainerDexRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

#[Route('/dex')]
class DexController extends AbstractController
{
    public function __construct(
        private readonly TrainerDexRepository $trainerDexRepository
    ) {
    }

    #[Route(path: '/{trainerExternalId}/list', methods: ['GET'])]
    public function list(
        string $trainerExternalId
    ): JsonResponse {
        /** @var string[][]|bool[][] $dexes */
        $dexes = iterator_to_array(
            $this->trainerDexRepository->getListQuery($trainerExternalId)
        );

        // Better with serializer ?
        return new JsonResponse($dexes);
    }

    #[Route(methods: ['PUT'], path: '/{trainerExternalId}/{dexSlug}')]
    public function put(
        Request $request,
        string $trainerExternalId,
        string $dexSlug
    ): Response {
        $json = $request->getContent();

        if (empty($json)) {
            throw new BadRequestHttpException();
        }

        /** @var bool[] */
        $content = json_decode((string) $json, true);

        try {
            $attributes = new TrainerDexAttributes($content);
        } catch (InvalidArgumentException  $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $this->trainerDexRepository->upsert($trainerExternalId, $dexSlug, $attributes);

        return new Response();
    }
}

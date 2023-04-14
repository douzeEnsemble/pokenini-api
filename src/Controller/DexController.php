<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
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
        string $trainerExternalId,
        Request $request,
    ): JsonResponse {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false)
        ]);

        /** @var string[][]|bool[][] $dex */
        $dex = iterator_to_array(
            $this->trainerDexRepository->getListQuery($trainerExternalId, $dexQueryOptions)
        );

        // Better with serializer ?
        return new JsonResponse($dex);
    }

    #[Route(methods: ['PUT'], path: '/{trainerExternalId}/{dexSlug}')]
    public function put(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
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

        $this->trainerDexRepository->set($trainerExternalId, $dexSlug, $attributes);

        return new Response();
    }
}

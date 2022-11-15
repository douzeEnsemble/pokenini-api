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

    #[Route(path: '/{trainerToken}/list', methods: ['GET'])]
    public function list(
        string $trainerToken
    ): JsonResponse {
        /** @var string[][]|bool[][] $dexes */
        $dexes = iterator_to_array(
            $this->trainerDexRepository->getListQuery($trainerToken)
        );

        // Better with serializer ?
        return new JsonResponse($dexes);
    }

    #[Route(methods: ['PATCH'], path: '/{trainerToken}/{dexSlug}')]
    public function update(
        Request $request,
        string $trainerToken,
        string $dexSlug
    ): Response {
        $this->upsert($trainerToken, $dexSlug, $request);

        return new Response();
    }

    #[Route(methods: ['PUT'], path: '/{trainerToken}/{dexSlug}')]
    public function create(
        Request $request,
        string $trainerToken,
        string $dexSlug
    ): Response {
        $this->upsert($trainerToken, $dexSlug, $request);

        return new Response('', Response::HTTP_CREATED);
    }

    private function upsert(string $trainerToken, string $dexSlug, Request $request): void
    {
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

        $this->trainerDexRepository->upsert($trainerToken, $dexSlug, $attributes);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PokedexRepository;
use App\Repository\TrainerDexRepository;
use App\Service\Album\AlbumDexService;
use App\Service\Album\AlbumReportService;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/album')]
class AlbumController extends AbstractController
{
    public function __construct(
        private readonly PokedexRepository $pokedexRepository,
        private readonly AlbumReportService $albumDexService,
        private readonly TrainerDexRepository $trainerDexRepository,
    ) {
    }

    #[Route(path: '/{trainerExternalId}/{dexSlug}', methods: ['GET'])]
    public function index(
        AlbumDexService $albumDexService,
        string $trainerExternalId,
        string $dexSlug
    ): JsonResponse {
        /** @var string[][]|int[][] $pokemons */
        $pokemons = iterator_to_array(
            $this->pokedexRepository->getListQuery($trainerExternalId, $dexSlug)
        );

        $report = $this->albumDexService->getReport($trainerExternalId, $dexSlug);

        $dex = $albumDexService->getData($trainerExternalId, $dexSlug);

        // Better with serializer ?
        return new JsonResponse([
            'dex' => $dex,
            'pokemons' => $pokemons,
            'report' => $report,
        ]);
    }

    #[Route(methods: ['PATCH'], path: '/{trainerExternalId}/{dexSlug}/{pokemonSlug}')]
    public function update(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
    ): Response {
        $this->upsert($trainerExternalId, $dexSlug, $pokemonSlug, $request);

        return new Response();
    }

    #[Route(methods: ['PUT'], path: '/{trainerExternalId}/{dexSlug}/{pokemonSlug}')]
    public function create(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
    ): Response {
        $this->upsert($trainerExternalId, $dexSlug, $pokemonSlug, $request);

        return new Response('', Response::HTTP_CREATED);
    }

    private function upsert(
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
        Request $request
    ): void {
        $content = $request->getContent();

        if (empty($content)) {
            throw new BadRequestHttpException();
        }

        /** @var string $catchStateSlug */
        $catchStateSlug = $content;

        try {
            $this->trainerDexRepository->insertIfNeeded(
                $trainerExternalId,
                $dexSlug,
            );

            $this->pokedexRepository->upsert(
                $trainerExternalId,
                $dexSlug,
                $pokemonSlug,
                $catchStateSlug,
            );
        } catch (NotNullConstraintViolationException $e) {
            throw new BadRequestHttpException();
        }
    }
}

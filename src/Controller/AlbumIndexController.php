<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Album\AlbumDexService;
use App\Service\Album\AlbumPokemonService;
use App\Service\Album\AlbumReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/album')]
class AlbumIndexController extends AbstractController
{
    #[Route(path: '/{trainerExternalId}/{dexSlug}', methods: ['GET'])]
    public function index(
        AlbumPokemonService $albumPokemonService,
        AlbumDexService $albumDexService,
        AlbumReportService $albumReportService,
        string $trainerExternalId,
        string $dexSlug
    ): JsonResponse {
        $pokemons = $albumPokemonService->get($trainerExternalId, $dexSlug);

        $report = $albumReportService->get($trainerExternalId, $dexSlug);

        $dex = $albumDexService->get($trainerExternalId, $dexSlug);

        // Better with serializer ?
        return new JsonResponse([
            'dex' => $dex,
            'pokemons' => $pokemons,
            'report' => $report,
        ]);
    }
}

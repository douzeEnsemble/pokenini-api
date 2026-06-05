<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumFilter\AlbumFiltersRequest;
use App\Factory\AlbumDexResponseFactory;
use App\Factory\AlbumPokemonResponseFactory;
use App\Service\Album\AlbumDexService;
use App\Service\Album\AlbumPokemonService;
use App\Service\Album\AlbumReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/album')]
final class AlbumIndexController extends AbstractController
{
    #[Route(path: '/{trainerExternalId}/{dexSlug}', methods: ['GET'])]
    public function index(
        AlbumPokemonService $albumPokemonService,
        AlbumDexService $albumDexService,
        AlbumReportService $albumReportService,
        string $trainerExternalId,
        string $dexSlug,
        Request $request,
        SerializerInterface $serializer,
    ): JsonResponse {
        $albumsFilters = AlbumFiltersRequest::albumFiltersFromRequest($request);

        $pokemons = $albumPokemonService->get(
            $trainerExternalId,
            $dexSlug,
            $albumsFilters
        );

        $report = $albumReportService->get(
            $trainerExternalId,
            $dexSlug,
            AlbumFilters::createFromArray([])
        );
        $filteredReport = $albumReportService->get(
            $trainerExternalId,
            $dexSlug,
            $albumsFilters
        );

        $dex = $albumDexService->get($trainerExternalId, $dexSlug);

        return JsonResponse::fromJsonString(
            $serializer->serialize(
                [
                    'dex' => empty($dex) ? null : AlbumDexResponseFactory::fromSqlRow($dex),
                    'pokemons' => AlbumPokemonResponseFactory::fromSqlRows($pokemons),
                    'report' => $report,
                    'filtered_report' => $filteredReport,
                ],
                'json',
            ),
        );
    }
}

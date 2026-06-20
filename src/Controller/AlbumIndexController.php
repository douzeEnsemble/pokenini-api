<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumFilter\AlbumFiltersRequest;
use App\DTO\Response\AlbumIndexResponse;
use App\Factory\AlbumDexResponseFactory;
use App\Factory\AlbumIndexResponseFactory;
use App\Factory\AlbumPokemonResponseFactory;
use App\Factory\AlbumReportResponseFactory;
use App\Service\Album\AlbumDexService;
use App\Service\Album\AlbumPokemonService;
use App\Service\Album\AlbumReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/album')]
final class AlbumIndexController extends AbstractController
{
    #[Route(path: '/{trainerExternalId}/{dexSlug}', methods: ['GET'])]
    #[Serialize]
    public function index(
        AlbumPokemonService $albumPokemonService,
        AlbumDexService $albumDexService,
        AlbumReportService $albumReportService,
        string $trainerExternalId,
        string $dexSlug,
        Request $request,
    ): AlbumIndexResponse {
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

        return AlbumIndexResponseFactory::fromParts(
            dex: empty($dex) ? null : AlbumDexResponseFactory::fromSqlRow($dex),
            pokemons: AlbumPokemonResponseFactory::fromSqlRows($pokemons),
            report: AlbumReportResponseFactory::fromReport($report),
            filteredReport: AlbumReportResponseFactory::fromReport($filteredReport),
        );
    }
}

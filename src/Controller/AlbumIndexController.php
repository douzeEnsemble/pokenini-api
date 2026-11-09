<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumFilter\AlbumFiltersRequest;
use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumIndexResponse;
use App\DTO\Response\LocalizedAlbumDexResponse;
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

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
#[Route('/album')]
final class AlbumIndexController extends AbstractController
{
    use LangQueryTrait;

    /**
     * Without "lang", the names are in English and in French (legacy shape). With "lang", the names of the
     * Pokémon are in every language and the others in that language.
     *
     * @param string[] $locales
     */
    #[Route(path: '/{trainerExternalId}/{dexSlug}', methods: ['GET'])]
    #[Serialize]
    public function index(
        AlbumPokemonService $albumPokemonService,
        AlbumDexService $albumDexService,
        AlbumReportService $albumReportService,
        string $trainerExternalId,
        string $dexSlug,
        Request $request,
        array $locales,
    ): AlbumIndexResponse {
        $lang = $this->getLang($request, $locales);
        $localized = null !== $lang;

        $albumsFilters = AlbumFiltersRequest::albumFiltersFromRequest($request);

        $pokemons = $albumPokemonService->get(
            $trainerExternalId,
            $dexSlug,
            $albumsFilters,
            $lang,
        );

        $report = $albumReportService->get(
            $trainerExternalId,
            $dexSlug,
            AlbumFilters::createFromArray([]),
            $lang,
        );
        $filteredReport = $albumReportService->get(
            $trainerExternalId,
            $dexSlug,
            $albumsFilters,
            $lang,
        );

        $dex = $albumDexService->get($trainerExternalId, $dexSlug, $lang);

        return AlbumIndexResponseFactory::fromParts(
            dex: self::buildDex($dex, $localized),
            pokemons: $localized
                ? AlbumPokemonResponseFactory::localizedFromSqlRows($pokemons)
                : AlbumPokemonResponseFactory::fromSqlRows($pokemons),
            report: $localized
                ? AlbumReportResponseFactory::localizedFromReport($report)
                : AlbumReportResponseFactory::fromReport($report),
            filteredReport: $localized
                ? AlbumReportResponseFactory::localizedFromReport($filteredReport)
                : AlbumReportResponseFactory::fromReport($filteredReport),
        );
    }

    /**
     * @param bool[]|string[] $dex
     */
    private static function buildDex(array $dex, bool $localized): AlbumDexResponse|LocalizedAlbumDexResponse|null
    {
        if ([] === $dex) {
            return null;
        }

        return $localized
            ? AlbumDexResponseFactory::localizedFromSqlRow($dex)
            : AlbumDexResponseFactory::fromSqlRow($dex);
    }
}

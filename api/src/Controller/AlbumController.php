<?php

namespace App\Controller;

use App\Helper\PokedexListReportHelper;
use App\Repository\CatchStateRepository;
use App\Repository\DexRepository;
use App\Repository\PokedexRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/album')]
class AlbumController extends AbstractController
{
    public function __construct(private readonly PokedexRepository $pokedexRepository)
    {
    }

    #[Route(methods: ['GET'], path: '/{dexSlug}')]
    public function index(
        DexRepository $dexRepository,
        CatchStateRepository $catchStateRepository,
        string $dexSlug
    ): JsonResponse {
        /** @var string[][]|int[][] $pokemons */
        $pokemons = iterator_to_array(
            $this->pokedexRepository->getListQueryFromDexSlug($dexSlug)
        );

        $report = PokedexListReportHelper::getReportFromPokedex(
            $pokemons,
            $catchStateRepository->getAll()
        );

        $dex = $dexRepository->getBySlug($dexSlug);

        // Better with serializer ?
        return new JsonResponse([
            'dex' => [
                'name' => $dex?->name,
                'french_name' => $dex?->frenchName,
                'is_shiny' => $dex?->isShiny,
                'is_private' => $dex?->isPrivate,
            ],
            'pokemons' => $pokemons,
            'report' => $report,
        ]);
    }

    #[Route(methods: ['PATCH'], path: '/{dexSlug}/{pokemonSlug}')]
    public function update(Request $request, string $dexSlug, string $pokemonSlug): Response
    {
        $this->upsert($dexSlug, $pokemonSlug, $request);

        return new Response();
    }

    #[Route(methods: ['PUT'], path: '/{dexSlug}/{pokemonSlug}')]
    public function create(Request $request, string $dexSlug, string $pokemonSlug): Response
    {
        $this->upsert($dexSlug, $pokemonSlug, $request);

        return new Response('', Response::HTTP_CREATED);
    }

    private function upsert(string $dexSlug, string $pokemonSlug, Request $request): void
    {
        $content = $request->getContent();

        if (empty($content)) {
            throw new BadRequestHttpException();
        }

        /** @var string $catchStateSlug */
        $catchStateSlug = $content;

        $this->pokedexRepository->upsertFromSlugs($dexSlug, $pokemonSlug, $catchStateSlug);
    }
}

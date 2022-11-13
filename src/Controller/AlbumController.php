<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Report\Report;
use App\DTO\Report\Statistic;
use App\Repository\DexAvailabilityRepository;
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
    public function __construct(
        private readonly PokedexRepository $pokedexRepository,
        private readonly DexAvailabilityRepository $dexAvailabilityRepository,
    ) {
    }

    #[Route(path: '/{trainerToken}/{dexSlug}', methods: ['GET'])]
    public function index(
        DexRepository $dexRepository,
        string $trainerToken,
        string $dexSlug
    ): JsonResponse {
        /** @var string[][]|int[][] $pokemons */
        $pokemons = iterator_to_array(
            $this->pokedexRepository->getListQuery($trainerToken, $dexSlug)
        );

        $report = $this->getReport($trainerToken, $dexSlug);

        $dex = $dexRepository->getBySlug($dexSlug);

        // Better with serializer ?
        return new JsonResponse([
            'dex' => [
                'name' => $dex?->name,
                'french_name' => $dex?->frenchName,
                'is_shiny' => $dex?->isShiny,
                'is_private' => $dex?->isPrivate,
                'is_display_form' => $dex?->isDisplayForm,
                'display_template' => $dex?->displayTemplate,
            ],
            'pokemons' => $pokemons,
            'report' => $report,
        ]);
    }

    #[Route(methods: ['PATCH'], path: '/{trainerToken}/{dexSlug}/{pokemonSlug}')]
    public function update(
        Request $request,
        string $trainerToken,
        string $dexSlug,
        string $pokemonSlug
    ): Response {
        $this->upsert($trainerToken, $dexSlug, $pokemonSlug, $request);

        return new Response();
    }

    #[Route(methods: ['PUT'], path: '/{trainerToken}/{dexSlug}/{pokemonSlug}')]
    public function create(
        Request $request,
        string $trainerToken,
        string $dexSlug,
        string $pokemonSlug
    ): Response {
        $this->upsert($trainerToken, $dexSlug, $pokemonSlug, $request);

        return new Response('', Response::HTTP_CREATED);
    }

    private function upsert(string $trainerToken, string $dexSlug, string $pokemonSlug, Request $request): void
    {
        $content = $request->getContent();

        if (empty($content)) {
            throw new BadRequestHttpException();
        }

        /** @var string $catchStateSlug */
        $catchStateSlug = $content;

        $this->pokedexRepository->upsert($trainerToken, $dexSlug, $pokemonSlug, $catchStateSlug);
    }

    private function getReport(string $trainerToken, string $dexSlug): Report
    {
        $totalCaught = 0;
        $detail = [];

        $total = $this->dexAvailabilityRepository->getTotal($dexSlug);
        $totalUncaught = $total;

        $catchStatesCounts = $this->pokedexRepository->getCatchStatesCounts($trainerToken, $dexSlug);
        foreach ($catchStatesCounts as $catchStatesCount) {
            $detail[] = new Statistic(
                (string) $catchStatesCount['slug'],
                (string) $catchStatesCount['name'],
                (string) $catchStatesCount['french_name'],
                (int) $catchStatesCount['count'],
            );

            if ('yes' === $catchStatesCount['slug']) {
                $totalCaught = (int) $catchStatesCount['count'];
            }

            if ('no' !== $catchStatesCount['slug']) {
                $totalUncaught -= $catchStatesCount['count'];
            }
        }

        return new Report($total, $totalCaught, $totalUncaught, $detail);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
use App\Repository\DexAvailabilitiesRepository;
use App\Repository\DexRepository;
use App\Repository\PokedexRepository;
use App\Repository\TrainerDexRepository;
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
        private readonly DexAvailabilitiesRepository $dexAvailabilitiesRepository,
        private readonly TrainerDexRepository $trainerDexRepository,
    ) {
    }

    #[Route(path: '/{trainerExternalId}/{dexSlug}', methods: ['GET'])]
    public function index(
        DexRepository $dexRepository,
        string $trainerExternalId,
        string $dexSlug
    ): JsonResponse {
        /** @var string[][]|int[][] $pokemons */
        $pokemons = iterator_to_array(
            $this->pokedexRepository->getListQuery($trainerExternalId, $dexSlug)
        );

        $report = $this->getReport($trainerExternalId, $dexSlug);

        $dex = $dexRepository->getData($trainerExternalId, $dexSlug);

        // Better with serializer ?
        return new JsonResponse([
            'dex' => $dex,
            'pokemons' => $pokemons,
            'report' => $report,
        ]);
    }

    #[Route(methods: ['PATCH'], path: '/{trainerExternalId}/{dexSlug}/{pokemonSlug}')]
    #[Route(methods: ['PATCH'], path: '/{trainerExternalId}/{dexSlug}-{trainerDexSlug}/{pokemonSlug}')]
    public function update(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
        string $trainerDexSlug = '',
    ): Response {
        $this->upsert($trainerExternalId, $dexSlug, $trainerDexSlug, $pokemonSlug, $request);

        return new Response();
    }

    #[Route(methods: ['PUT'], path: '/{trainerExternalId}/{dexSlug}/{pokemonSlug}')]
    #[Route(methods: ['PUT'], path: '/{trainerExternalId}/{dexSlug}-{trainerDexSlug}/{pokemonSlug}')]
    public function create(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
        string $trainerDexSlug = '',
    ): Response {
        $this->upsert($trainerExternalId, $dexSlug, $trainerDexSlug, $pokemonSlug, $request);

        return new Response('', Response::HTTP_CREATED);
    }

    private function upsert(
        string $trainerExternalId,
        string $dexSlug,
        string $trainerDexSlug,
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
                $trainerDexSlug
            );

            $this->pokedexRepository->upsert(
                $trainerExternalId,
                $dexSlug,
                $pokemonSlug,
                $catchStateSlug,
                $trainerDexSlug,
            );
        } catch (NotNullConstraintViolationException $e) {
            throw new BadRequestHttpException();
        }
    }

    private function getReport(string $trainerExternalId, string $dexSlug): Report
    {
        $totalCaught = 0;
        $detail = [];

        $total = $this->dexAvailabilitiesRepository->getTotal($dexSlug);
        $totalUncaught = $total;

        $catchStatesCounts = $this->pokedexRepository->getCatchStatesCounts($trainerExternalId, $dexSlug);
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

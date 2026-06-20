<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\ElectionEloResponse;
use App\DTO\Response\ElectionMetricsResponse;
use App\DTO\TrainerPokemonEloQueryOptions;
use App\Factory\ElectionEloResponseFactory;
use App\Factory\ElectionMetricsResponseFactory;
use App\Repository\TrainerPokemonEloRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/election')]
final class TrainerPokemonEloController extends AbstractController
{
    /** @return ElectionEloResponse[] */
    #[Route(path: '/top', methods: ['GET'])]
    #[Serialize]
    public function top(
        Request $request,
        TrainerPokemonEloRepository $trainerPokemonEloRepository,
    ): array {
        /** @var array<int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloQueryOptions($params);

        $rows = $trainerPokemonEloRepository->getTopN(
            $queryOptions->trainerExternalId,
            $queryOptions->dexSlug,
            $queryOptions->electionSlug,
            $queryOptions->count,
        );

        return ElectionEloResponseFactory::fromSqlRows($rows);
    }

    #[Route(path: '/metrics', methods: ['GET'])]
    #[Serialize]
    public function metrics(
        Request $request,
        TrainerPokemonEloRepository $trainerPokemonEloRepository,
    ): ElectionMetricsResponse {
        /** @var array<int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloQueryOptions($params);

        $metrics = $trainerPokemonEloRepository->getMetrics(
            $queryOptions->trainerExternalId,
            $queryOptions->dexSlug,
            $queryOptions->electionSlug,
        );

        return ElectionMetricsResponseFactory::fromArray($metrics);
    }
}

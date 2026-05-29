<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TrainerPokemonEloQueryOptions;
use App\Factory\ElectionEloResponseFactory;
use App\Repository\TrainerPokemonEloRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/election')]
final class TrainerPokemonEloController extends AbstractController
{
    #[Route(path: '/top', methods: ['GET'])]
    public function top(
        Request $request,
        TrainerPokemonEloRepository $trainerPokemonEloRepository,
        SerializerInterface $serializer,
    ): JsonResponse {
        /** @var array<int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloQueryOptions($params);

        $rows = $trainerPokemonEloRepository->getTopN(
            $queryOptions->trainerExternalId,
            $queryOptions->dexSlug,
            $queryOptions->electionSlug,
            $queryOptions->count,
        );

        $responses = ElectionEloResponseFactory::fromSqlRows($rows);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }

    #[Route(path: '/metrics', methods: ['GET'])]
    public function metrics(
        Request $request,
        TrainerPokemonEloRepository $trainerPokemonEloRepository,
    ): JsonResponse {
        /** @var array<int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloQueryOptions($params);

        // Better with serializer ?
        return new JsonResponse(
            $trainerPokemonEloRepository->getMetrics(
                $queryOptions->trainerExternalId,
                $queryOptions->dexSlug,
                $queryOptions->electionSlug,
            )
        );
    }
}

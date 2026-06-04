<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TrainerPokemonEloListQueryOptions;
use App\Factory\ElectionPokemonResponseFactory;
use App\Service\GetNPokemonsToChooseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/pokemons')]
final class PokemonsController extends AbstractController
{
    #[Route(path: '/to_choose', methods: ['GET'])]
    public function getNPokemonsToChoose(
        Request $request,
        GetNPokemonsToChooseService $getNPokemonsToChooseService,
        SerializerInterface $serializer,
    ): JsonResponse {
        /** @var array<array<string>|int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloListQueryOptions($params);

        $list = $getNPokemonsToChooseService->getNPokemonsToChoose($queryOptions);

        $response = ElectionPokemonResponseFactory::fromElectionPokemonsList($list);

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
        );
    }
}

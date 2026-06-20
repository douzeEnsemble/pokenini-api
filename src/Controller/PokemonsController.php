<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\ElectionPokemonsListResponse;
use App\DTO\TrainerPokemonEloListQueryOptions;
use App\Factory\ElectionPokemonResponseFactory;
use App\Service\GetNPokemonsToChooseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pokemons')]
final class PokemonsController extends AbstractController
{
    #[Route(path: '/to_choose', methods: ['GET'])]
    #[Serialize]
    public function getNPokemonsToChoose(
        Request $request,
        GetNPokemonsToChooseService $getNPokemonsToChooseService,
    ): ElectionPokemonsListResponse {
        /** @var array<array<string>|int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloListQueryOptions($params);

        $list = $getNPokemonsToChooseService->getNPokemonsToChoose($queryOptions);

        return ElectionPokemonResponseFactory::fromElectionPokemonsList($list);
    }
}

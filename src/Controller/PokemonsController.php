<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TrainerPokemonEloListQueryOptions;
use App\Service\GetNPokemonsToChooseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    ): Response {
        /** @var array<array<string>|int|string> $params */
        $params = $request->query->all();
        $queryOptions = new TrainerPokemonEloListQueryOptions($params);

        $list = $getNPokemonsToChooseService->getNPokemonsToChoose($queryOptions);

        return new Response($serializer->serialize(
            $list,
            'json'
        ));
    }
}

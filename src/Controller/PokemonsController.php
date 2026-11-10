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
    use LangQueryTrait;

    /**
     * @param string[] $locales the enabled locales
     */
    public function __construct(
        private readonly array $locales,
    ) {}

    #[Route(path: '/to_choose', methods: ['GET'])]
    #[Serialize]
    public function getNPokemonsToChoose(
        Request $request,
        GetNPokemonsToChooseService $getNPokemonsToChooseService,
    ): ElectionPokemonsListResponse {
        $lang = $this->getLang($request, $this->locales);

        /** @var array<array<string>|int|string> $params */
        $params = $request->query->all();
        // The language is not a filter
        unset($params['lang']);
        $queryOptions = new TrainerPokemonEloListQueryOptions($params);

        $list = $getNPokemonsToChooseService->getNPokemonsToChoose($queryOptions, $lang);

        return ElectionPokemonResponseFactory::fromElectionPokemonsList($list, null !== $lang);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use App\DTO\Response\PokemonAvailabilitiesResponse;
use App\DTO\Response\PokemonDebugResponse;
use App\Entity\Pokemon;
use App\Factory\PokemonAvailabilitiesResponseFactory;
use App\Factory\PokemonDebugResponseFactory;
use App\Service\CollectionsAvailabilitiesService;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Service\GamesAvailabilitiesService;
use App\Service\GamesShiniesAvailabilitiesService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/debogage/pokemon')]
final class DebugPokemonController extends AbstractController
{
    #[Route(path: '/{slug}', methods: ['GET'])]
    #[Serialize]
    public function pokemon(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
    ): PokemonDebugResponse {
        return PokemonDebugResponseFactory::fromPokemon($pokemon);
    }

    #[Route(path: '/{slug}/caches', methods: ['DELETE'])]
    public function pokemonCaches(
        GamesAvailabilitiesService $gamesAvailabilitiesService,
        GamesShiniesAvailabilitiesService $gamesShiniesAvailabilitiesService,
        GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        GameBundlesShiniesAvailabilitiesService $gameBundlesShiniesAvailabilitiesService,
        CollectionsAvailabilitiesService $collectionsAvailabilitiesService,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
    ): Response {
        $gamesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gamesShiniesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gameBundlesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $gameBundlesShiniesAvailabilitiesService->cleanCacheFromPokemon($pokemon);
        $collectionsAvailabilitiesService->cleanCacheFromPokemon($pokemon);

        return new Response();
    }

    #[Route(path: '/{slug}/availabilities', methods: ['GET'])]
    #[Serialize]
    public function pokemonAvailabilities(
        GamesAvailabilitiesService $gamesAvailabilitiesService,
        GamesShiniesAvailabilitiesService $gamesShiniesAvailabilitiesService,
        GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        GameBundlesShiniesAvailabilitiesService $gameBundlesShiniesAvailabilitiesService,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
    ): PokemonAvailabilitiesResponse {
        return PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            $gamesAvailabilitiesService->getFromPokemon($pokemon),
            $gamesShiniesAvailabilitiesService->getFromPokemon($pokemon),
            $gameBundlesAvailabilitiesService->getFromPokemon($pokemon),
            $gameBundlesShiniesAvailabilitiesService->getFromPokemon($pokemon),
        );
    }
}

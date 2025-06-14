<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use App\Entity\Pokemon;
use App\Service\CollectionsAvailabilitiesService;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Service\GamesAvailabilitiesService;
use App\Service\GamesShiniesAvailabilitiesService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/debogage/pokemon')]
class DebugPokemonController extends AbstractDebugController
{
    #[Route(path: '/{slug}', methods: ['GET'])]
    public function pokemon(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
    ): Response {
        return new Response(
            $this->serialize($pokemon),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json',
            ]
        );
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
    public function pokemonAvailabilities(
        GamesAvailabilitiesService $gamesAvailabilitiesService,
        GamesShiniesAvailabilitiesService $gamesShiniesAvailabilitiesService,
        GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        GameBundlesShiniesAvailabilitiesService $gameBundlesShiniesAvailabilitiesService,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Pokemon $pokemon,
    ): Response {
        $gamesAvailabilities = $gamesAvailabilitiesService->getFromPokemon($pokemon);
        $gamesShiniesAvailabilities = $gamesShiniesAvailabilitiesService->getFromPokemon($pokemon);
        $gameBundlesAvailabilities = $gameBundlesAvailabilitiesService->getFromPokemon($pokemon);
        $gameBundlesShiniesAvailabilities = $gameBundlesShiniesAvailabilitiesService->getFromPokemon($pokemon);

        return new Response(
            $this->serialize([
                'gamesAvailabilities' => $gamesAvailabilities->all(),
                'gamesShiniesAvailabilities' => $gamesShiniesAvailabilities->all(),
                'gameBundlesAvailabilities' => $gameBundlesAvailabilities->all(),
                'gameBundlesShiniesAvailabilities' => $gameBundlesShiniesAvailabilities->all(),
            ]),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }
}

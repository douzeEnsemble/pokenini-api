<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Repository\DexAvailabilitiesRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/debogage/dex')]
class DebugDexController extends AbstractDebugController
{
    #[Route(path: '/{slug}', methods: ['GET'])]
    public function dex(
        Dex $dex,
    ): Response {
        return new Response(
            $this->serialize($dex),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json'
            ]
        );
    }

    #[Route(path: '/{slug}/availabilities', methods: ['GET'])]
    public function dexAvailabilities(
        Dex $dex,
        DexAvailabilitiesRepository $dexAvailabilitiesRepository,
    ): Response {
        $dexAvailabilities = $dexAvailabilitiesRepository->findBy(['dex' => $dex]);

        $pokemons = [];
        /** @var DexAvailability $dexAvailability */
        foreach ($dexAvailabilities as $dexAvailability) {
            $pokemons[] = $dexAvailability->pokemon->slug;
        }

        return new Response(
            $this->serialize($pokemons),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json'
            ]
        );
    }
}

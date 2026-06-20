<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use App\DTO\Response\DexAvailabilitiesResponse;
use App\DTO\Response\DexDebugResponse;
use App\Entity\Dex;
use App\Factory\DexAvailabilitiesResponseFactory;
use App\Factory\DexDebugResponseFactory;
use App\Service\DexAvailabilitiesService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/debogage/dex')]
final class DebugDexController extends AbstractController
{
    #[Route(path: '/{slug}', methods: ['GET'])]
    #[Serialize]
    public function dex(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Dex $dex,
    ): DexDebugResponse {
        return DexDebugResponseFactory::fromDex($dex);
    }

    #[Route(path: '/{slug}/availabilities', methods: ['GET'])]
    #[Serialize]
    public function dexAvailabilities(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Dex $dex,
        DexAvailabilitiesService $dexAvailabilitiesService,
    ): DexAvailabilitiesResponse {
        $dexAvailabilities = $dexAvailabilitiesService->getByDex($dex);

        return DexAvailabilitiesResponseFactory::fromDexAvailabilities($dexAvailabilities);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use App\Entity\Dex;
use App\Factory\DexAvailabilitiesResponseFactory;
use App\Factory\DexDebugResponseFactory;
use App\Service\DexAvailabilitiesService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/debogage/dex')]
final class DebugDexController extends AbstractController
{
    #[Route(path: '/{slug}', methods: ['GET'])]
    public function dex(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Dex $dex,
        SerializerInterface $serializer,
    ): JsonResponse {
        $response = DexDebugResponseFactory::fromDex($dex);

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
        );
    }

    #[Route(path: '/{slug}/availabilities', methods: ['GET'])]
    public function dexAvailabilities(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Dex $dex,
        DexAvailabilitiesService $dexAvailabilitiesService,
        SerializerInterface $serializer,
    ): JsonResponse {
        $dexAvailabilities = $dexAvailabilitiesService->getByDex($dex);

        $response = DexAvailabilitiesResponseFactory::fromDexAvailabilities($dexAvailabilities);

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
        );
    }
}

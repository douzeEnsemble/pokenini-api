<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DexBannerLayersService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/istration/dex-banner-layers')]
final class DexBannerLayersController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(DexBannerLayersService $service): JsonResponse
    {
        $layers = $service->getAll();

        return new JsonResponse([] === $layers ? new \stdClass() : $layers);
    }
}

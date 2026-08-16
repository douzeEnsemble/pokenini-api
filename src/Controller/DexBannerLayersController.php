<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DexBannerLayersService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/istration/dex-banner-layers')]
final class DexBannerLayersController extends AbstractController
{
    /**
     * @return array<string, string[]>
     */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(DexBannerLayersService $service): array
    {
        return $service->getAll();
    }
}

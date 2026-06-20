<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\GameBundleResponse;
use App\Factory\GameBundleResponseFactory;
use App\Service\GameBundlesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/game_bundles')]
final class GameBundlesController extends AbstractController
{
    /** @return GameBundleResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(GameBundlesService $service): array
    {
        $gameBundles = $service->getAll();

        return GameBundleResponseFactory::fromSqlRows($gameBundles);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
use App\DTO\Response\WhichShinyGameDexResponse;
use App\Factory\WhichShinyGameResponseFactory;
use App\Service\WhichShinyGameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/which_shiny_game/{trainerExternalId}')]
final class WhichShinyGameDexController extends AbstractController
{
    public function __construct(
        private readonly WhichShinyGameService $whichShinyGameService,
    ) {}

    /** @return WhichShinyGameDexResponse[] */
    #[Route(path: '/dex', methods: ['GET'])]
    #[Serialize]
    public function list(
        string $trainerExternalId,
        Request $request,
    ): array {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false),
            'include_premium_dex' => $request->query->getBoolean('include_premium_dex', false),
        ]);

        return WhichShinyGameResponseFactory::fromDexRows(
            $this->whichShinyGameService->getPlayableDexList($dexQueryOptions),
            $this->whichShinyGameService->getInProgressGamesByDex($trainerExternalId),
        );
    }
}

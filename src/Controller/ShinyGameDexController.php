<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
use App\DTO\Response\ShinyGameDexResponse;
use App\Factory\ShinyGameResponseFactory;
use App\Service\ShinyGameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/shiny_game/{trainerExternalId}')]
final class ShinyGameDexController extends AbstractController
{
    public function __construct(
        private readonly ShinyGameService $shinyGameService,
    ) {}

    /** @return ShinyGameDexResponse[] */
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

        return ShinyGameResponseFactory::fromDexRows(
            $this->shinyGameService->getPlayableDexList($dexQueryOptions),
            $this->shinyGameService->getInProgressGamesByDex($trainerExternalId),
        );
    }
}

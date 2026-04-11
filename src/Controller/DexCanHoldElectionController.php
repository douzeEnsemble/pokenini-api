<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
use App\Service\DexCanHoldElectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dex')]
final class DexCanHoldElectionController extends AbstractController
{
    #[Route(path: '/can_hold_election', methods: ['GET'])]
    public function list(
        Request $request,
        DexCanHoldElectionService $service,
    ): JsonResponse {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false),
            'include_premium_dex' => $request->query->getBoolean('include_premium_dex', false),
        ]);

        $dex = $service->get($dexQueryOptions);

        // Better with serializer ?
        return new JsonResponse($dex);
    }
}

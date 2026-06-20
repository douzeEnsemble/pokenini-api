<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
use App\DTO\Response\DexResponse;
use App\Factory\DexResponseFactory;
use App\Service\DexCanHoldElectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dex')]
final class DexCanHoldElectionController extends AbstractController
{
    /** @return DexResponse[] */
    #[Route(path: '/can_hold_election', methods: ['GET'])]
    #[Serialize]
    public function list(
        Request $request,
        DexCanHoldElectionService $service,
    ): array {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false),
            'include_premium_dex' => $request->query->getBoolean('include_premium_dex', false),
        ]);

        $dex = $service->get($dexQueryOptions);

        return DexResponseFactory::fromSqlRows($dex);
    }
}

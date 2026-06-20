<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\CatchStateResponse;
use App\Factory\CatchStateResponseFactory;
use App\Service\CatchStatesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/catch_states')]
final class CatchStatesController extends AbstractController
{
    /** @return CatchStateResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(CatchStatesService $service): array
    {
        $catchStates = $service->getAll();

        return CatchStateResponseFactory::fromSqlRows($catchStates);
    }
}

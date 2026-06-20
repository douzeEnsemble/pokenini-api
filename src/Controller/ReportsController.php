<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\ReportResponse;
use App\Factory\ReportResponseFactory;
use App\Service\PokedexService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reports')]
final class ReportsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(PokedexService $service): ReportResponse
    {
        return ReportResponseFactory::fromServiceArrays(
            $service->getCatchStateCountsDefinedByTrainer(),
            $service->getDexUsage(),
            $service->getCatchStateUsage(),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\ReportResponseFactory;
use App\Service\PokedexService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/reports')]
final class ReportsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(PokedexService $service, SerializerInterface $serializer): JsonResponse
    {
        $report = ReportResponseFactory::fromServiceArrays(
            $service->getCatchStateCountsDefinedByTrainer(),
            $service->getDexUsage(),
            $service->getCatchStateUsage(),
        );

        return JsonResponse::fromJsonString(
            $serializer->serialize($report, 'json'),
        );
    }
}

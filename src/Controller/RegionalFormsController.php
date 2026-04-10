<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\RegionalFormsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/forms/regional')]
class RegionalFormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        RegionalFormsService $service
    ): JsonResponse {
        $forms = $service->getAll();

        // Better with serializer ?
        return new JsonResponse($forms);
    }
}

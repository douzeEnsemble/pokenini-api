<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryFormsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/forms/category')]
final class CategoryFormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CategoryFormsService $service
    ): JsonResponse {
        $forms = $service->getAll();

        // Better with serializer ?
        return new JsonResponse($forms);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CategoryFormsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forms/category')]
class CategoryFormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CategoryFormsRepository $formsRepository
    ): JsonResponse {
        $forms = $formsRepository->getAll();

        // Better with serializer ?
        return new JsonResponse($forms);
    }
}

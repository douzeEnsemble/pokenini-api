<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SpecialFormsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forms/special')]
class SpecialFormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        SpecialFormsRepository $formsRepository
    ): JsonResponse {
        $forms = $formsRepository->getAll();

        // Better with serializer ?
        return new JsonResponse($forms);
    }
}

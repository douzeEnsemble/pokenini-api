<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\TypesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/types')]
class TypesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        TypesRepository $typesRepository
    ): JsonResponse {
        $types = $typesRepository->getAll();

        // Better with serializer ?
        return new JsonResponse($types);
    }
}

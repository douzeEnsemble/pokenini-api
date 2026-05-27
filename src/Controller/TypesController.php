<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\TypeResponseFactory;
use App\Service\TypesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/types')]
final class TypesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        TypesService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $types = $service->getAll();

        $responses = TypeResponseFactory::fromSqlRows($types);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}

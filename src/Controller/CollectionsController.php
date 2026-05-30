<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\CollectionResponseFactory;
use App\Service\CollectionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/collections')]
final class CollectionsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CollectionsService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $collections = $service->getAll();

        $responses = CollectionResponseFactory::fromSqlRows($collections);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}

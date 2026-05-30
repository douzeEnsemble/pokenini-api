<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\FormResponseFactory;
use App\Service\RegionalFormsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/forms/regional')]
final class RegionalFormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        RegionalFormsService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $forms = $service->getAll();

        $responses = FormResponseFactory::fromSqlRows($forms);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}

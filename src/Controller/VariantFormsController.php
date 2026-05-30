<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\FormResponseFactory;
use App\Service\VariantFormsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/forms/variant')]
final class VariantFormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        VariantFormsService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $forms = $service->getAll();

        $responses = FormResponseFactory::fromSqlRows($forms);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}

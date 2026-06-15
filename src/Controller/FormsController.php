<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\FormTypesResponse;
use App\Factory\FormResponseFactory;
use App\Service\CategoryFormsService;
use App\Service\RegionalFormsService;
use App\Service\SpecialFormsService;
use App\Service\VariantFormsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/forms')]
final class FormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CategoryFormsService $categoryFormsService,
        RegionalFormsService $regionalFormsService,
        SpecialFormsService $specialFormsService,
        VariantFormsService $variantFormsService,
        SerializerInterface $serializer,
    ): JsonResponse {
        $response = new FormTypesResponse(
            category: FormResponseFactory::fromSqlRows($categoryFormsService->getAll()),
            regional: FormResponseFactory::fromSqlRows($regionalFormsService->getAll()),
            special: FormResponseFactory::fromSqlRows($specialFormsService->getAll()),
            variant: FormResponseFactory::fromSqlRows($variantFormsService->getAll()),
        );

        return JsonResponse::fromJsonString(
            $serializer->serialize($response, 'json'),
        );
    }
}

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
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/forms')]
final class FormsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(
        CategoryFormsService $categoryFormsService,
        RegionalFormsService $regionalFormsService,
        SpecialFormsService $specialFormsService,
        VariantFormsService $variantFormsService,
    ): FormTypesResponse {
        return new FormTypesResponse(
            category: FormResponseFactory::fromSqlRows($categoryFormsService->getAll()),
            regional: FormResponseFactory::fromSqlRows($regionalFormsService->getAll()),
            special: FormResponseFactory::fromSqlRows($specialFormsService->getAll()),
            variant: FormResponseFactory::fromSqlRows($variantFormsService->getAll()),
        );
    }
}

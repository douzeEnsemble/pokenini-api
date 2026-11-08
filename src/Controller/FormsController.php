<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\FormTypesResponse;
use App\DTO\Response\LocalizedFormTypesResponse;
use App\Factory\FormResponseFactory;
use App\Service\CategoryFormsService;
use App\Service\RegionalFormsService;
use App\Service\SpecialFormsService;
use App\Service\VariantFormsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/forms')]
final class FormsController extends AbstractController
{
    use LangQueryTrait;

    /**
     * Without "lang", each form has its name in English and in French (legacy shape).
     * With "lang", each form has a single name, in that language.
     *
     * @param string[] $locales
     */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(
        Request $request,
        CategoryFormsService $categoryFormsService,
        RegionalFormsService $regionalFormsService,
        SpecialFormsService $specialFormsService,
        VariantFormsService $variantFormsService,
        array $locales,
    ): FormTypesResponse|LocalizedFormTypesResponse {
        $lang = $this->getLang($request, $locales);

        if (null === $lang) {
            return new FormTypesResponse(
                category: FormResponseFactory::fromSqlRows($categoryFormsService->getAll()),
                regional: FormResponseFactory::fromSqlRows($regionalFormsService->getAll()),
                special: FormResponseFactory::fromSqlRows($specialFormsService->getAll()),
                variant: FormResponseFactory::fromSqlRows($variantFormsService->getAll()),
            );
        }

        return new LocalizedFormTypesResponse(
            category: FormResponseFactory::localizedFromSqlRows($categoryFormsService->getAll($lang)),
            regional: FormResponseFactory::localizedFromSqlRows($regionalFormsService->getAll($lang)),
            special: FormResponseFactory::localizedFromSqlRows($specialFormsService->getAll($lang)),
            variant: FormResponseFactory::localizedFromSqlRows($variantFormsService->getAll($lang)),
        );
    }
}

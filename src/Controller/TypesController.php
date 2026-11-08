<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\LocalizedTypeResponse;
use App\DTO\Response\TypeResponse;
use App\Factory\TypeResponseFactory;
use App\Service\TypesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/types')]
final class TypesController extends AbstractController
{
    use LangQueryTrait;

    /**
     * Without "lang", each type has its name in English and in French (legacy shape).
     * With "lang", each type has a single name, in that language.
     *
     * @param string[] $locales
     *
     * @return LocalizedTypeResponse[]|TypeResponse[]
     */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(
        Request $request,
        TypesService $service,
        array $locales,
    ): array {
        $lang = $this->getLang($request, $locales);

        $types = $service->getAll($lang);

        if (null === $lang) {
            return TypeResponseFactory::fromSqlRows($types);
        }

        return TypeResponseFactory::localizedFromSqlRows($types);
    }
}

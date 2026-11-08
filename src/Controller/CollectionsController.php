<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\CollectionResponse;
use App\DTO\Response\LocalizedCollectionResponse;
use App\Factory\CollectionResponseFactory;
use App\Service\CollectionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collections')]
final class CollectionsController extends AbstractController
{
    use LangQueryTrait;

    /**
     * Without "lang", each item has its name in English and in French (legacy shape).
     * With "lang", each item has a single name, in that language.
     *
     * @param string[] $locales
     *
     * @return CollectionResponse[]|LocalizedCollectionResponse[]
     */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(
        Request $request,
        CollectionsService $service,
        array $locales,
    ): array {
        $lang = $this->getLang($request, $locales);

        $collections = $service->getAll($lang);

        if (null === $lang) {
            return CollectionResponseFactory::fromSqlRows($collections);
        }

        return CollectionResponseFactory::localizedFromSqlRows($collections);
    }
}

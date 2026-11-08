<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\CatchStateResponse;
use App\DTO\Response\LocalizedCatchStateResponse;
use App\Factory\CatchStateResponseFactory;
use App\Service\CatchStatesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/catch_states')]
final class CatchStatesController extends AbstractController
{
    use LangQueryTrait;

    /**
     * Without "lang", each item has its name in English and in French (legacy shape).
     * With "lang", each item has a single name, in that language.
     *
     * @param string[] $locales
     *
     * @return CatchStateResponse[]|LocalizedCatchStateResponse[]
     */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(
        Request $request,
        CatchStatesService $service,
        array $locales,
    ): array {
        $lang = $this->getLang($request, $locales);

        $catchStates = $service->getAll($lang);

        if (null === $lang) {
            return CatchStateResponseFactory::fromSqlRows($catchStates);
        }

        return CatchStateResponseFactory::localizedFromSqlRows($catchStates);
    }
}

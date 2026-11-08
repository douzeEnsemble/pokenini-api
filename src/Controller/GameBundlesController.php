<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\GameBundleResponse;
use App\DTO\Response\LocalizedGameBundleResponse;
use App\Factory\GameBundleResponseFactory;
use App\Service\GameBundlesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/game_bundles')]
final class GameBundlesController extends AbstractController
{
    use LangQueryTrait;

    /**
     * Without "lang", each item has its name in English and in French (legacy shape).
     * With "lang", each item has a single name, in that language.
     *
     * @param string[] $locales
     *
     * @return GameBundleResponse[]|LocalizedGameBundleResponse[]
     */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(
        Request $request,
        GameBundlesService $service,
        array $locales,
    ): array {
        $lang = $this->getLang($request, $locales);

        $gameBundles = $service->getAll($lang);

        if (null === $lang) {
            return GameBundleResponseFactory::fromSqlRows($gameBundles);
        }

        return GameBundleResponseFactory::localizedFromSqlRows($gameBundles);
    }
}

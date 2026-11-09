<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\LocalizedPokemonCreditResponse;
use App\DTO\Response\PokemonCreditResponse;
use App\Factory\PokemonCreditResponseFactory;
use App\Service\ImageCreditsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/credits')]
final class ImageCreditsController extends AbstractController
{
    use LangQueryTrait;

    /**
     * Without "lang", each Pokémon has its name in English and in French (legacy shape).
     * With "lang", each Pokémon has a single name, in that language.
     *
     * @param string[] $locales
     *
     * @return LocalizedPokemonCreditResponse[]|PokemonCreditResponse[]
     */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(Request $request, ImageCreditsService $service, array $locales): array
    {
        $lang = $this->getLang($request, $locales);

        $rows = $service->getAllByPokemon($lang);

        if (null === $lang) {
            return PokemonCreditResponseFactory::fromRows($rows);
        }

        return PokemonCreditResponseFactory::localizedFromRows($rows);
    }
}

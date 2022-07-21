<?php

namespace App\Controller;

use App\Repository\DexRepository;
use App\Repository\PokedexRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/pokedex')]
class PokedexController extends AbstractController
{
    #[Route('')]
    public function index(
        Request $request,
        PokedexRepository $pokedexRepository,
        DexRepository $dexRepository,
    ): JsonResponse {
        /** @var string $dexSlug */
        $dexSlug = $request->query->get('dex_slug');

        if (empty($dexSlug)) {
            throw new BadRequestHttpException();
        }

        $pokemons = iterator_to_array(
            $pokedexRepository->getQueryFromDexSlug($dexSlug)
        );

        $dex = $dexRepository->getBySlug($dexSlug);

        // Better with serializer ?
        return new JsonResponse([
            'dex' => [
                'name' => $dex?->name
            ],
            'pokemons' => $pokemons,
        ]);
    }
}

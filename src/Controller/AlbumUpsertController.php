<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PokedexService;
use App\Service\TrainerDexService;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/album')]
class AlbumUpsertController extends AbstractController
{
    public function __construct(
        private readonly PokedexService $pokedexService,
        private readonly TrainerDexService $trainerDexService,
    ) {}

    #[Route(methods: ['PATCH'], path: '/{trainerExternalId}/{dexSlug}/{pokemonSlug}')]
    public function update(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
    ): Response {
        $this->upsert($trainerExternalId, $dexSlug, $pokemonSlug, $request);

        return new Response();
    }

    #[Route(methods: ['PUT'], path: '/{trainerExternalId}/{dexSlug}/{pokemonSlug}')]
    public function create(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
    ): Response {
        $this->upsert($trainerExternalId, $dexSlug, $pokemonSlug, $request);

        return new Response('', Response::HTTP_CREATED);
    }

    private function upsert(
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
        Request $request
    ): void {
        $content = $request->getContent();

        if (!$content) {
            throw new BadRequestHttpException();
        }

        /** @var string $catchStateSlug */
        $catchStateSlug = $content;

        try {
            $this->trainerDexService->insertIfNeeded(
                $trainerExternalId,
                $dexSlug,
            );

            $this->pokedexService->upsert(
                $trainerExternalId,
                $dexSlug,
                $pokemonSlug,
                $catchStateSlug,
            );
        } catch (NotNullConstraintViolationException $e) {
            throw new BadRequestHttpException();
        }
    }
}

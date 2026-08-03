<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\AlbumUpsertResponse;
use App\Service\PokedexService;
use App\Service\TrainerDexService;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/album')]
final class AlbumUpsertController extends AbstractController
{
    public function __construct(
        private readonly PokedexService $pokedexService,
        private readonly TrainerDexService $trainerDexService,
    ) {}

    #[Route(methods: ['PATCH'], path: '/{trainerExternalId}/{dexSlug}/{pokemonSlug}')]
    #[Serialize]
    public function update(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
    ): AlbumUpsertResponse {
        return $this->upsert($trainerExternalId, $dexSlug, $pokemonSlug, $request);
    }

    #[Route(methods: ['PUT'], path: '/{trainerExternalId}/{dexSlug}/{pokemonSlug}')]
    #[Serialize(code: Response::HTTP_CREATED)]
    public function create(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
    ): AlbumUpsertResponse {
        return $this->upsert($trainerExternalId, $dexSlug, $pokemonSlug, $request);
    }

    private function upsert(
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
        Request $request
    ): AlbumUpsertResponse {
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

            $updatedDexSlugs = $this->pokedexService->upsert(
                $trainerExternalId,
                $dexSlug,
                $pokemonSlug,
                $catchStateSlug,
            );
        } catch (NotNullConstraintViolationException $e) {
            throw new BadRequestHttpException();
        }

        return new AlbumUpsertResponse($updatedDexSlugs);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
use App\DTO\Response\TrainerDexResponse;
use App\DTO\TrainerDexAttributes;
use App\Factory\TrainerDexResponseFactory;
use App\Service\Album\AlbumReportService;
use App\Service\TrainerDexService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dex')]
final class DexController extends AbstractController
{
    public function __construct(
        private readonly TrainerDexService $trainerDexService,
        private readonly AlbumReportService $albumReportService,
    ) {}

    /** @return TrainerDexResponse[] */
    #[Route(path: '/{trainerExternalId}/list', methods: ['GET'])]
    #[Serialize]
    public function list(
        string $trainerExternalId,
        Request $request,
    ): array {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false),
            'include_premium_dex' => $request->query->getBoolean('include_premium_dex', false),
        ]);

        $dex = iterator_to_array(
            $this->trainerDexService->getListQuery($trainerExternalId, $dexQueryOptions)
        );

        $reports = $this->albumReportService->getBatch($trainerExternalId);

        return TrainerDexResponseFactory::fromSqlRows($dex, $reports);
    }

    #[Route(methods: ['PUT'], path: '/{trainerExternalId}/{dexSlug}')]
    public function put(
        Request $request,
        string $trainerExternalId,
        string $dexSlug,
    ): Response {
        $json = $request->getContent();

        if (!$json) {
            throw new BadRequestHttpException();
        }

        /** @var bool[] */
        $content = json_decode($json, true);

        try {
            $attributes = new TrainerDexAttributes($content);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $this->trainerDexService->set($trainerExternalId, $dexSlug, $attributes);

        return new Response();
    }
}

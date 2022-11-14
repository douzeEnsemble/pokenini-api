<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\DexRepository;
use App\Repository\TrainerDexRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dex')]
class DexController extends AbstractController
{
    #[Route(path: '/{trainerToken}/list', methods: ['GET'])]
    public function list(
        TrainerDexRepository $trainerDexRepository,
        string $trainerToken
    ): JsonResponse {
        /** @var string[][]|bool[][] $dexes */
        $dexes = iterator_to_array(
            $trainerDexRepository->getListQuery($trainerToken)
        );

        // Better with serializer ?
        return new JsonResponse($dexes);
    }
}

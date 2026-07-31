<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\PokemonCreditResponse;
use App\Factory\PokemonCreditResponseFactory;
use App\Service\ImageCreditsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/credits')]
final class ImageCreditsController extends AbstractController
{
    /** @return PokemonCreditResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(ImageCreditsService $service): array
    {
        return PokemonCreditResponseFactory::fromRows($service->getAllByPokemon());
    }
}

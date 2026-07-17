<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\ImageCreditResponse;
use App\Factory\ImageCreditResponseFactory;
use App\Service\ImageCreditsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/credits')]
final class ImageCreditsController extends AbstractController
{
    /** @return ImageCreditResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(ImageCreditsService $service): array
    {
        $credits = $service->getAll();

        return ImageCreditResponseFactory::fromSqlRows($credits);
    }
}

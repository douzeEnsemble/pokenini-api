<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\ImageCreditGroupResponse;
use App\Factory\ImageCreditGroupResponseFactory;
use App\Service\ImageCreditsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/credits')]
final class ImageCreditsController extends AbstractController
{
    /** @return ImageCreditGroupResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(ImageCreditsService $service): array
    {
        return ImageCreditGroupResponseFactory::fromGroupedRows($service->getAllGroupedBySource());
    }
}

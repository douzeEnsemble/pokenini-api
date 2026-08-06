<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\VersionResponse;
use App\Service\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/version')]
final class VersionController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(VersionService $service): VersionResponse
    {
        return new VersionResponse($service->getVersion(), $service->getUpdatedAt());
    }
}

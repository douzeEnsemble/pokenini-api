<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\TypeResponse;
use App\Factory\TypeResponseFactory;
use App\Service\TypesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/types')]
final class TypesController extends AbstractController
{
    /** @return TypeResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(TypesService $service): array
    {
        $types = $service->getAll();

        return TypeResponseFactory::fromSqlRows($types);
    }
}

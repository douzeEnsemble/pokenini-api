<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\CollectionResponse;
use App\Factory\CollectionResponseFactory;
use App\Service\CollectionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collections')]
final class CollectionsController extends AbstractController
{
    /** @return CollectionResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(CollectionsService $service): array
    {
        $collections = $service->getAll();

        return CollectionResponseFactory::fromSqlRows($collections);
    }
}

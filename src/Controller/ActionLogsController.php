<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\ActionLogResponse;
use App\Factory\ActionLogResponseFactory;
use App\Service\ActionLogsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/action_logs')]
final class ActionLogsController extends AbstractController
{
    /** @return ActionLogResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(ActionLogsService $service): array
    {
        $rows = $service->getLastests();

        return ActionLogResponseFactory::fromSqlRows($rows);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ActionLogsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/action_logs')]
class ActionLogsController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route(path: '', methods: ['GET'])]
    public function get(ActionLogsRepository $repo): JsonResponse
    {
        return new JsonResponse($repo->getLastests());
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\MessengerActionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/messenger_actions')]
class MessengerActionsController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route(path: '', methods: ['GET'])]
    public function get(MessengerActionsRepository $repo): JsonResponse
    {
        return new JsonResponse($repo->getLastests());
    }
}

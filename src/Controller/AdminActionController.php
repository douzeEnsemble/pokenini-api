<?php

declare(strict_types=1);

namespace App\Controller;

use App\ActionStarter\ActionCatalog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/istration')]
final class AdminActionController extends AbstractController
{
    public function __construct(
        private readonly ActionCatalog $catalog,
        private readonly MessageBusInterface $bus,
    ) {}

    #[Route(path: '/actions', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return new JsonResponse($this->catalog->all());
    }

    #[Route(
        path: '/{type}/{name}',
        requirements: ['type' => 'update|calculate', 'name' => '[a-z_]+'],
        methods: ['POST'],
    )]
    public function start(string $type, string $name): Response
    {
        $definition = $this->catalog->find($type, $name);

        if (null === $definition) {
            throw $this->createNotFoundException("Unknown action: {$type} {$name}");
        }

        $this->bus->dispatch($definition->starter->start());

        return new Response('', Response::HTTP_CREATED);
    }
}

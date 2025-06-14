<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ElectionVote;
use App\Service\ElectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/election')]
class ElectionVoteController extends AbstractController
{
    #[Route(path: '/vote', methods: ['POST'])]
    public function vote(
        Request $request,
        ElectionService $electionService,
        SerializerInterface $serializer,
    ): Response {
        $json = $request->getContent();

        if (!$json) {
            throw new BadRequestHttpException();
        }

        /** @var string[]|string[][] */
        $content = json_decode($json, true);

        if (!$content) {
            throw new BadRequestHttpException();
        }

        try {
            $electionVote = new ElectionVote($content);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $results = $electionService->vote($electionVote);

        return new Response($serializer->serialize(
            $results,
            'json'
        ));
    }
}

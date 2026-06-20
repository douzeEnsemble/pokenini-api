<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ElectionVote;
use App\DTO\Response\ElectionVoteResultResponse;
use App\Factory\ElectionVoteResultResponseFactory;
use App\Service\ElectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/election')]
final class ElectionVoteController extends AbstractController
{
    #[Route(path: '/vote', methods: ['POST'])]
    #[Serialize]
    public function vote(
        Request $request,
        ElectionService $electionService,
    ): ElectionVoteResultResponse {
        $json = $request->getContent();

        if (!$json) {
            throw new BadRequestHttpException();
        }

        /** @var array<string, mixed> */
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

        return ElectionVoteResultResponseFactory::fromElectionVoteResult($results);
    }
}

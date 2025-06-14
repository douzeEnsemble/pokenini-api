<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ElectionVote;
use App\DTO\ElectionVoteResult;

class ElectionService
{
    public function __construct(
        private readonly ElectionUpdateEloService $updateEloService,
    ) {}

    public function vote(ElectionVote $electionVote): ElectionVoteResult
    {
        $pokemonsElo = $this->updateEloService->update($electionVote);

        return new ElectionVoteResult(
            $electionVote,
            $pokemonsElo,
        );
    }
}

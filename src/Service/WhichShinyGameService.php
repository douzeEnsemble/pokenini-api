<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\DexQueryOptions;
use App\DTO\WhichShinyGameAnswer;
use App\Entity\WhichShinyGame;
use App\Exception\InvalidWhichShinyGameRequestException;
use App\Exception\WhichShinyGameConflictException;
use App\Exception\WhichShinyGameNotFoundException;
use App\Repository\WhichShinyGameRepository;
use Random\Randomizer;
use Symfony\Component\Uid\Uuid;

class WhichShinyGameService
{
    public function __construct(
        private readonly WhichShinyGameRepository $repository,
        private readonly Randomizer $randomizer,
    ) {}

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getPlayableDexList(DexQueryOptions $options): array
    {
        return $this->repository->getPlayableDexList($options);
    }

    /**
     * @return array<string, array<string, mixed>> keyed by dex slug
     */
    public function getInProgressGamesByDex(string $trainerExternalId): array
    {
        return $this->repository->getInProgressGamesByDex($trainerExternalId);
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getGames(string $trainerExternalId): array
    {
        return $this->repository->getGames($trainerExternalId);
    }

    /**
     * @param null|int $roundCount the number of Pokémon to play, randomly picked in the dex; null to play them all
     *
     * @return array<string, mixed>
     */
    public function create(string $trainerExternalId, string $dexSlug, ?int $roundCount = null): array
    {
        $dexId = $this->repository->getPlayableDexId($dexSlug);

        if (null === $dexId) {
            throw new WhichShinyGameNotFoundException("Dex {$dexSlug} can not be played");
        }

        $pokemonIds = $this->repository->getPlayablePokemonIds($dexId);

        if ([] === $pokemonIds) {
            throw new InvalidWhichShinyGameRequestException("Dex {$dexSlug} has no Pokémon to play with");
        }

        // Heads or tails: the shiny image is shown first or second
        $rounds = array_map(
            fn (string $pokemonId): array => [
                'pokemon_id' => $pokemonId,
                'shiny_slot' => $this->randomizer->getInt(1, 2),
            ],
            array_slice($this->randomizer->shuffleArray($pokemonIds), 0, $roundCount),
        );

        $gameId = (string) Uuid::v4();

        $this->repository->startGame($gameId, $trainerExternalId, $dexId, $rounds);

        return $this->getGame($trainerExternalId, $gameId);
    }

    /**
     * @return array{
     *  game: array<string, mixed>,
     *  round: null|array<string, mixed>,
     * }
     */
    public function get(string $trainerExternalId, string $gameId): array
    {
        return [
            'game' => $this->getGame($trainerExternalId, $gameId),
            'round' => $this->repository->getNextRound($gameId),
        ];
    }

    /**
     * @return array{
     *  game: array<string, mixed>,
     *  rounds: array<array-key, array<string, mixed>>,
     * }
     */
    public function getRounds(string $trainerExternalId, string $gameId): array
    {
        return [
            'game' => $this->getGame($trainerExternalId, $gameId),
            'rounds' => $this->repository->getAnsweredRounds($gameId),
        ];
    }

    /**
     * @return array{
     *  position: int,
     *  is_correct: bool,
     *  chosen_slot: int,
     *  shiny_slot: int,
     *  game: array<string, mixed>,
     * }
     */
    public function answer(string $trainerExternalId, string $gameId, WhichShinyGameAnswer $answer): array
    {
        $game = $this->getGame($trainerExternalId, $gameId);

        if (WhichShinyGame::STATUS_IN_PROGRESS !== $game['status']) {
            throw new WhichShinyGameConflictException('This game is over');
        }

        $round = $this->repository->getRoundAt($gameId, $answer->position);

        if (null === $round) {
            throw new WhichShinyGameNotFoundException("There is no round {$answer->position} in this game");
        }

        if (null !== $round['chosen_slot']) {
            throw new WhichShinyGameConflictException("Round {$answer->position} has already been answered");
        }

        /** @var int $shinySlot */
        $shinySlot = $round['shiny_slot'];
        $isCorrect = $answer->slot === $shinySlot;

        if (!$this->repository->answerRound($gameId, $answer->position, $answer->slot, $isCorrect)) {
            throw new WhichShinyGameConflictException("Round {$answer->position} has already been answered");
        }

        return [
            'position' => $answer->position,
            'is_correct' => $isCorrect,
            'chosen_slot' => $answer->slot,
            'shiny_slot' => $shinySlot,
            'game' => $this->getGame($trainerExternalId, $gameId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getGame(string $trainerExternalId, string $gameId): array
    {
        $game = $this->repository->getGame($trainerExternalId, $gameId);

        if (null === $game) {
            throw new WhichShinyGameNotFoundException("Game {$gameId} not found");
        }

        return $game;
    }
}

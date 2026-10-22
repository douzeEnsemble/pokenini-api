<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\DexQueryOptions;
use App\DTO\ShinyGameAnswer;
use App\Entity\ShinyGame;
use App\Exception\InvalidShinyGameRequestException;
use App\Exception\ShinyGameConflictException;
use App\Exception\ShinyGameNotFoundException;
use App\Repository\ShinyGameRepository;
use Random\Randomizer;
use Symfony\Component\Uid\Uuid;

class ShinyGameService
{
    public function __construct(
        private readonly ShinyGameRepository $repository,
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
            throw new ShinyGameNotFoundException("Dex {$dexSlug} can not be played");
        }

        $pokemonIds = $this->repository->getPlayablePokemonIds($dexId);

        if ([] === $pokemonIds) {
            throw new InvalidShinyGameRequestException("Dex {$dexSlug} has no Pokémon to play with");
        }

        // Heads or tails: the regular or the shiny image is shown
        $rounds = array_map(
            fn (string $pokemonId): array => [
                'pokemon_id' => $pokemonId,
                'is_shiny' => 1 === $this->randomizer->getInt(0, 1),
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
     *  position: int,
     *  is_correct: bool,
     *  chosen_shiny: bool,
     *  is_shiny: bool,
     *  game: array<string, mixed>,
     * }
     */
    public function answer(string $trainerExternalId, string $gameId, ShinyGameAnswer $answer): array
    {
        $game = $this->getGame($trainerExternalId, $gameId);

        if (ShinyGame::STATUS_IN_PROGRESS !== $game['status']) {
            throw new ShinyGameConflictException('This game is over');
        }

        $round = $this->repository->getRoundAt($gameId, $answer->position);

        if (null === $round) {
            throw new ShinyGameNotFoundException("There is no round {$answer->position} in this game");
        }

        if (null !== $round['chosen_shiny']) {
            throw new ShinyGameConflictException("Round {$answer->position} has already been answered");
        }

        $isShiny = true === $round['is_shiny'];
        $isCorrect = $answer->isShiny === $isShiny;

        if (!$this->repository->answerRound($gameId, $answer->position, $answer->isShiny, $isCorrect)) {
            throw new ShinyGameConflictException("Round {$answer->position} has already been answered");
        }

        return [
            'position' => $answer->position,
            'is_correct' => $isCorrect,
            'chosen_shiny' => $answer->isShiny,
            'is_shiny' => $isShiny,
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
            throw new ShinyGameNotFoundException("Game {$gameId} not found");
        }

        return $game;
    }
}

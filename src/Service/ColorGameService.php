<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ColorGameAnswer;
use App\DTO\DexQueryOptions;
use App\Entity\ColorGame;
use App\Exception\ColorGameConflictException;
use App\Exception\ColorGameNotFoundException;
use App\Exception\InvalidColorGameRequestException;
use App\Repository\ColorGameRepository;
use Random\Randomizer;
use Symfony\Component\Uid\Uuid;

class ColorGameService
{
    /** The colour set at this position is the right one */
    private const int RIGHT_SET_POSITION = 1;

    public function __construct(
        private readonly ColorGameRepository $repository,
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
        $dex = $this->repository->getPlayableDex($dexSlug);

        if (null === $dex) {
            throw new ColorGameNotFoundException("Dex {$dexSlug} can not be played");
        }

        $pokemons = $this->repository->getPlayablePokemons($dex['id'], $dex['is_shiny']);

        if ([] === $pokemons) {
            throw new InvalidColorGameRequestException("Dex {$dexSlug} has no Pokémon to play with");
        }

        $rounds = array_map(
            fn (array $pokemon): array => [
                'pokemon_id' => $pokemon['pokemon_id'],
                'choices_order' => $this->randomizer->shuffleArray(
                    array_map(intval(...), explode(',', $pokemon['positions'])),
                ),
            ],
            array_slice($this->randomizer->shuffleArray($pokemons), 0, $roundCount),
        );

        $gameId = (string) Uuid::v4();

        $this->repository->startGame($gameId, $trainerExternalId, $dex['id'], $dex['is_shiny'], $rounds);

        return $this->getGame($trainerExternalId, $gameId);
    }

    /**
     * @return array{
     *  game: array<string, mixed>,
     *  round: null|array<string, mixed>,
     *  color_sets: array<array-key, array<string, mixed>>,
     * }
     */
    public function get(string $trainerExternalId, string $gameId): array
    {
        $game = $this->getGame($trainerExternalId, $gameId);

        $round = $this->repository->getNextRound($gameId);

        $colorSets = [];

        if (null !== $round) {
            /** @var scalar $pokemonId */
            $pokemonId = $round['pokemon_id'];

            $colorSets = $this->repository->getColorSets((string) $pokemonId, (bool) $game['is_shiny']);
        }

        return [
            'game' => $game,
            'round' => $round,
            'color_sets' => $colorSets,
        ];
    }

    /**
     * @return array{
     *  game: array<string, mixed>,
     *  rounds: list<array{
     *   round: array<string, mixed>,
     *   color_sets: list<array<string, mixed>>,
     *   correct_choice: int,
     *  }>,
     * }
     */
    public function getRounds(string $trainerExternalId, string $gameId): array
    {
        $game = $this->getGame($trainerExternalId, $gameId);

        $colorSets = [];
        foreach ($this->repository->getAnsweredRoundsColorSets($gameId) as $colorSet) {
            /** @var int|string $roundPosition numeric string keys are cast to int by PHP */
            $roundPosition = $colorSet['round_position'];

            $colorSets[$roundPosition][] = $colorSet;
        }

        $rounds = [];
        foreach ($this->repository->getAnsweredRounds($gameId) as $round) {
            /** @var int|string $position numeric string keys are cast to int by PHP */
            $position = $round['position'];

            $rounds[] = [
                'round' => $round,
                'color_sets' => $colorSets[$position] ?? [],
                'correct_choice' => self::getCorrectChoice(self::getChoicesOrder($round)),
            ];
        }

        return [
            'game' => $game,
            'rounds' => $rounds,
        ];
    }

    /**
     * @return array{
     *  position: int,
     *  is_correct: bool,
     *  chosen_choice: int,
     *  correct_choice: int,
     *  game: array<string, mixed>,
     * }
     */
    public function answer(string $trainerExternalId, string $gameId, ColorGameAnswer $answer): array
    {
        $game = $this->getGame($trainerExternalId, $gameId);

        if (ColorGame::STATUS_IN_PROGRESS !== $game['status']) {
            throw new ColorGameConflictException('This game is over');
        }

        $round = $this->repository->getRoundAt($gameId, $answer->position);

        if (null === $round) {
            throw new ColorGameNotFoundException("There is no round {$answer->position} in this game");
        }

        if (null !== $round['chosen_choice']) {
            throw new ColorGameConflictException("Round {$answer->position} has already been answered");
        }

        $choicesOrder = self::getChoicesOrder($round);

        if (!isset($choicesOrder[$answer->choice])) {
            throw new InvalidColorGameRequestException("Choice {$answer->choice} does not exist");
        }

        $correctChoice = self::getCorrectChoice($choicesOrder);
        $isCorrect = $answer->choice === $correctChoice;

        if (!$this->repository->answerRound($gameId, $answer->position, $answer->choice, $isCorrect)) {
            throw new ColorGameConflictException("Round {$answer->position} has already been answered");
        }

        return [
            'position' => $answer->position,
            'is_correct' => $isCorrect,
            'chosen_choice' => $answer->choice,
            'correct_choice' => $correctChoice,
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
            throw new ColorGameNotFoundException("Game {$gameId} not found");
        }

        return $game;
    }

    /**
     * @param array<string, mixed> $round
     *
     * @return list<int> the position of the colour set shown at each choice index
     */
    private static function getChoicesOrder(array $round): array
    {
        /** @var scalar $choicesOrder */
        $choicesOrder = $round['choices_order'];

        return array_map(intval(...), explode(',', (string) $choicesOrder));
    }

    /**
     * @param list<int> $choicesOrder
     */
    private static function getCorrectChoice(array $choicesOrder): int
    {
        return array_flip($choicesOrder)[self::RIGHT_SET_POSITION];
    }
}

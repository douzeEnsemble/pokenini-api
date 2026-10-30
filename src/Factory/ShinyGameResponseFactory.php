<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ShinyGameAnsweredRoundResponse;
use App\DTO\Response\ShinyGameAnswerResponse;
use App\DTO\Response\ShinyGameDexResponse;
use App\DTO\Response\ShinyGameDexSummaryResponse;
use App\DTO\Response\ShinyGamePokemonResponse;
use App\DTO\Response\ShinyGameResponse;
use App\DTO\Response\ShinyGameRoundResponse;
use App\DTO\Response\ShinyGameRoundsResponse;
use App\DTO\Response\ShinyGameStateResponse;

final class ShinyGameResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromGameRow(array $row): ShinyGameResponse
    {
        /** @var scalar $gameId */
        $gameId = $row['id'];

        /** @var scalar $dexSlug */
        $dexSlug = $row['dex_slug'];

        /** @var scalar $dexName */
        $dexName = $row['dex_name'];

        /** @var scalar $dexFrenchName */
        $dexFrenchName = $row['dex_french_name'];

        /** @var scalar $status */
        $status = $row['status'];

        /** @var scalar $roundCount */
        $roundCount = $row['round_count'];

        /** @var scalar $answeredCount */
        $answeredCount = $row['answered_count'];

        /** @var scalar $correctCount */
        $correctCount = $row['correct_count'];

        /** @var null|scalar $results */
        $results = $row['results'];

        /** @var scalar $startedAt */
        $startedAt = $row['started_at'];

        /** @var null|scalar $lastPlayedAt */
        $lastPlayedAt = $row['last_played_at'];

        /** @var null|scalar $endedAt */
        $endedAt = $row['ended_at'];

        return new ShinyGameResponse(
            id: (string) $gameId,
            dex: new ShinyGameDexSummaryResponse(
                slug: (string) $dexSlug,
                name: (string) $dexName,
                frenchName: (string) $dexFrenchName,
            ),
            status: (string) $status,
            roundCount: (int) $roundCount,
            answeredCount: (int) $answeredCount,
            correctCount: (int) $correctCount,
            results: GameResultsFactory::fromString((string) $results),
            startedAt: self::formatDate((string) $startedAt),
            lastPlayedAt: null === $lastPlayedAt ? null : self::formatDate((string) $lastPlayedAt),
            endedAt: null === $endedAt ? null : self::formatDate((string) $endedAt),
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return ShinyGameResponse[]
     */
    public static function fromGameRows(array $rows): array
    {
        return array_values(array_map(self::fromGameRow(...), $rows));
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     * @param array<string, array<array-key, mixed>>    $games in progress games, keyed by dex slug
     *
     * @return ShinyGameDexResponse[]
     */
    public static function fromDexRows(array $rows, array $games): array
    {
        return array_values(array_map(
            static function (array $row) use ($games): ShinyGameDexResponse {
                /** @var string $slug */
                $slug = $row['slug'];

                /** @var scalar $name */
                $name = $row['name'];

                /** @var scalar $frenchName */
                $frenchName = $row['french_name'];

                /** @var scalar $isReleased */
                $isReleased = $row['is_released'];

                /** @var scalar $isPremium */
                $isPremium = $row['is_premium'];

                /** @var scalar $description */
                $description = $row['description'];

                /** @var scalar $frenchDescription */
                $frenchDescription = $row['french_description'];

                /** @var scalar $playableCount */
                $playableCount = $row['playable_count'];

                $game = $games[$slug] ?? null;

                return new ShinyGameDexResponse(
                    slug: $slug,
                    name: (string) $name,
                    frenchName: (string) $frenchName,
                    isReleased: (bool) $isReleased,
                    isPremium: (bool) $isPremium,
                    description: (string) $description,
                    frenchDescription: (string) $frenchDescription,
                    playableCount: (int) $playableCount,
                    currentGame: null === $game ? null : self::fromGameRow($game),
                    category: DexCategoryResponseFactory::fromSqlRow($row),
                );
            },
            $rows,
        ));
    }

    /**
     * @param array{
     *  game: array<array-key, mixed>,
     *  round: null|array<array-key, mixed>,
     * } $state
     */
    public static function fromState(array $state): ShinyGameStateResponse
    {
        return new ShinyGameStateResponse(
            game: self::fromGameRow($state['game']),
            round: null === $state['round'] ? null : self::fromRoundRow($state['round']),
        );
    }

    /**
     * @param array{
     *  position: int,
     *  is_correct: bool,
     *  chosen_shiny: bool,
     *  is_shiny: bool,
     *  game: array<array-key, mixed>,
     * } $answer
     */
    public static function fromAnswer(array $answer): ShinyGameAnswerResponse
    {
        return new ShinyGameAnswerResponse(
            position: $answer['position'],
            isCorrect: $answer['is_correct'],
            chosenShiny: $answer['chosen_shiny'],
            isShiny: $answer['is_shiny'],
            game: self::fromGameRow($answer['game']),
        );
    }

    /**
     * @param array{
     *  game: array<array-key, mixed>,
     *  rounds: array<array-key, array<array-key, mixed>>,
     * } $rounds
     */
    public static function fromRounds(array $rounds): ShinyGameRoundsResponse
    {
        return new ShinyGameRoundsResponse(
            game: self::fromGameRow($rounds['game']),
            rounds: array_values(array_map(
                static function (array $row): ShinyGameAnsweredRoundResponse {
                    $round = self::fromRoundRow($row);

                    /** @var scalar $isCorrect */
                    $isCorrect = $row['is_correct'];

                    /** @var scalar $chosenShiny */
                    $chosenShiny = $row['chosen_shiny'];

                    return new ShinyGameAnsweredRoundResponse(
                        position: $round->position,
                        pokemon: $round->pokemon,
                        isCorrect: (bool) $isCorrect,
                        isShiny: $round->isShiny,
                        chosenShiny: (bool) $chosenShiny,
                    );
                },
                $rounds['rounds'],
            )),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private static function fromRoundRow(array $row): ShinyGameRoundResponse
    {
        /** @var scalar $position */
        $position = $row['position'];

        /** @var scalar $isShiny */
        $isShiny = $row['is_shiny'];

        /** @var scalar $slug */
        $slug = $row['pokemon_slug'];

        /** @var scalar $name */
        $name = $row['pokemon_name'];

        /** @var scalar $frenchName */
        $frenchName = $row['pokemon_french_name'];

        /** @var scalar $simplifiedName */
        $simplifiedName = $row['pokemon_simplified_name'];

        /** @var scalar $simplifiedFrenchName */
        $simplifiedFrenchName = $row['pokemon_simplified_french_name'];

        /** @var scalar $formsLabel */
        $formsLabel = $row['pokemon_forms_label'];

        /** @var scalar $formsFrenchLabel */
        $formsFrenchLabel = $row['pokemon_forms_french_label'];

        /** @var scalar $nationalDexNumber */
        $nationalDexNumber = $row['pokemon_national_dex_number'];

        /** @var scalar $icon */
        $icon = $row['pokemon_icon'];

        return new ShinyGameRoundResponse(
            position: (int) $position,
            pokemon: new ShinyGamePokemonResponse(
                slug: (string) $slug,
                name: (string) $name,
                frenchName: (string) $frenchName,
                simplifiedName: (string) $simplifiedName,
                simplifiedFrenchName: (string) $simplifiedFrenchName,
                formsLabel: (string) $formsLabel,
                formsFrenchLabel: (string) $formsFrenchLabel,
                nationalDexNumber: (int) $nationalDexNumber,
                icon: (string) $icon,
            ),
            isShiny: (bool) $isShiny,
        );
    }

    private static function formatDate(string $date): string
    {
        return new \DateTimeImmutable($date, new \DateTimeZone('UTC'))->format(\DATE_ATOM);
    }
}

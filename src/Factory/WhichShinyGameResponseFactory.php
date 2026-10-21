<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\WhichShinyGameAnswerResponse;
use App\DTO\Response\WhichShinyGameDexResponse;
use App\DTO\Response\WhichShinyGameDexSummaryResponse;
use App\DTO\Response\WhichShinyGamePokemonResponse;
use App\DTO\Response\WhichShinyGameResponse;
use App\DTO\Response\WhichShinyGameRoundResponse;
use App\DTO\Response\WhichShinyGameStateResponse;

final class WhichShinyGameResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromGameRow(array $row): WhichShinyGameResponse
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

        /** @var scalar $startedAt */
        $startedAt = $row['started_at'];

        /** @var null|scalar $lastPlayedAt */
        $lastPlayedAt = $row['last_played_at'];

        /** @var null|scalar $endedAt */
        $endedAt = $row['ended_at'];

        return new WhichShinyGameResponse(
            id: (string) $gameId,
            dex: new WhichShinyGameDexSummaryResponse(
                slug: (string) $dexSlug,
                name: (string) $dexName,
                frenchName: (string) $dexFrenchName,
            ),
            status: (string) $status,
            roundCount: (int) $roundCount,
            answeredCount: (int) $answeredCount,
            correctCount: (int) $correctCount,
            startedAt: self::formatDate((string) $startedAt),
            lastPlayedAt: null === $lastPlayedAt ? null : self::formatDate((string) $lastPlayedAt),
            endedAt: null === $endedAt ? null : self::formatDate((string) $endedAt),
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return WhichShinyGameResponse[]
     */
    public static function fromGameRows(array $rows): array
    {
        return array_values(array_map(self::fromGameRow(...), $rows));
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     * @param array<string, array<array-key, mixed>>    $games in progress games, keyed by dex slug
     *
     * @return WhichShinyGameDexResponse[]
     */
    public static function fromDexRows(array $rows, array $games): array
    {
        return array_values(array_map(
            static function (array $row) use ($games): WhichShinyGameDexResponse {
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

                return new WhichShinyGameDexResponse(
                    slug: $slug,
                    name: (string) $name,
                    frenchName: (string) $frenchName,
                    isReleased: (bool) $isReleased,
                    isPremium: (bool) $isPremium,
                    description: (string) $description,
                    frenchDescription: (string) $frenchDescription,
                    playableCount: (int) $playableCount,
                    currentGame: null === $game ? null : self::fromGameRow($game),
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
    public static function fromState(array $state): WhichShinyGameStateResponse
    {
        return new WhichShinyGameStateResponse(
            game: self::fromGameRow($state['game']),
            round: null === $state['round'] ? null : self::fromRoundRow($state['round']),
        );
    }

    /**
     * @param array{
     *  position: int,
     *  is_correct: bool,
     *  chosen_slot: int,
     *  shiny_slot: int,
     *  game: array<array-key, mixed>,
     * } $answer
     */
    public static function fromAnswer(array $answer): WhichShinyGameAnswerResponse
    {
        return new WhichShinyGameAnswerResponse(
            position: $answer['position'],
            isCorrect: $answer['is_correct'],
            chosenSlot: $answer['chosen_slot'],
            shinySlot: $answer['shiny_slot'],
            game: self::fromGameRow($answer['game']),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private static function fromRoundRow(array $row): WhichShinyGameRoundResponse
    {
        /** @var scalar $position */
        $position = $row['position'];

        /** @var scalar $shinySlot */
        $shinySlot = $row['shiny_slot'];

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

        return new WhichShinyGameRoundResponse(
            position: (int) $position,
            pokemon: new WhichShinyGamePokemonResponse(
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
            shinySlot: (int) $shinySlot,
        );
    }

    private static function formatDate(string $date): string
    {
        return new \DateTimeImmutable($date, new \DateTimeZone('UTC'))->format(\DATE_ATOM);
    }
}

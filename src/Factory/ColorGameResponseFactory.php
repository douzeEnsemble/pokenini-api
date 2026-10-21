<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ColorGameAnswerResponse;
use App\DTO\Response\ColorGameChoiceResponse;
use App\DTO\Response\ColorGameDexResponse;
use App\DTO\Response\ColorGameDexSummaryResponse;
use App\DTO\Response\ColorGamePokemonResponse;
use App\DTO\Response\ColorGameResponse;
use App\DTO\Response\ColorGameRoundResponse;
use App\DTO\Response\ColorGameStateResponse;

final class ColorGameResponseFactory
{
    private const string COLORS_SEPARATOR = ';';

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromGameRow(array $row): ColorGameResponse
    {
        /** @var scalar $gameId */
        $gameId = $row['id'];

        /** @var scalar $dexSlug */
        $dexSlug = $row['dex_slug'];

        /** @var scalar $dexName */
        $dexName = $row['dex_name'];

        /** @var scalar $dexFrenchName */
        $dexFrenchName = $row['dex_french_name'];

        /** @var scalar $isShiny */
        $isShiny = $row['is_shiny'];

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

        return new ColorGameResponse(
            id: (string) $gameId,
            dex: new ColorGameDexSummaryResponse(
                slug: (string) $dexSlug,
                name: (string) $dexName,
                frenchName: (string) $dexFrenchName,
            ),
            isShiny: (bool) $isShiny,
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
     * @return ColorGameResponse[]
     */
    public static function fromGameRows(array $rows): array
    {
        return array_values(array_map(self::fromGameRow(...), $rows));
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     * @param array<string, array<array-key, mixed>>    $games in progress games, keyed by dex slug
     *
     * @return ColorGameDexResponse[]
     */
    public static function fromDexRows(array $rows, array $games): array
    {
        return array_values(array_map(
            static function (array $row) use ($games): ColorGameDexResponse {
                /** @var string $slug */
                $slug = $row['slug'];

                /** @var scalar $name */
                $name = $row['name'];

                /** @var scalar $frenchName */
                $frenchName = $row['french_name'];

                /** @var scalar $isShiny */
                $isShiny = $row['is_shiny'];

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

                return new ColorGameDexResponse(
                    slug: $slug,
                    name: (string) $name,
                    frenchName: (string) $frenchName,
                    isShiny: (bool) $isShiny,
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
     *  color_sets: array<array-key, array<array-key, mixed>>,
     * } $state
     */
    public static function fromState(array $state): ColorGameStateResponse
    {
        return new ColorGameStateResponse(
            game: self::fromGameRow($state['game']),
            round: null === $state['round'] ? null : self::fromRoundRow($state['round'], $state['color_sets']),
        );
    }

    /**
     * @param array{
     *  position: int,
     *  is_correct: bool,
     *  chosen_choice: int,
     *  correct_choice: int,
     *  game: array<array-key, mixed>,
     * } $answer
     */
    public static function fromAnswer(array $answer): ColorGameAnswerResponse
    {
        return new ColorGameAnswerResponse(
            position: $answer['position'],
            isCorrect: $answer['is_correct'],
            chosenChoice: $answer['chosen_choice'],
            correctChoice: $answer['correct_choice'],
            game: self::fromGameRow($answer['game']),
        );
    }

    /**
     * @param array<array-key, mixed>                   $row
     * @param array<array-key, array<array-key, mixed>> $colorSets
     */
    private static function fromRoundRow(array $row, array $colorSets): ColorGameRoundResponse
    {
        /** @var scalar $position */
        $position = $row['position'];

        /** @var scalar $choicesOrder */
        $choicesOrder = $row['choices_order'];

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

        $setsByPosition = [];
        foreach ($colorSets as $colorSet) {
            /** @var int|string $setPosition numeric string keys are cast to int by PHP */
            $setPosition = $colorSet['position'];

            $setsByPosition[$setPosition] = $colorSet;
        }

        $choices = [];
        foreach (explode(',', (string) $choicesOrder) as $index => $setPosition) {
            $choices[] = self::buildChoice($index, $setsByPosition[$setPosition] ?? []);
        }

        return new ColorGameRoundResponse(
            position: (int) $position,
            pokemon: new ColorGamePokemonResponse(
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
            choices: $choices,
        );
    }

    /**
     * @param array<array-key, mixed> $colorSet empty when the set does not exist anymore
     */
    private static function buildChoice(int $index, array $colorSet): ColorGameChoiceResponse
    {
        /** @var scalar $codes */
        $codes = $colorSet['codes'] ?? '';

        /** @var scalar $names */
        $names = $colorSet['names'] ?? '';

        /** @var scalar $frenchNames */
        $frenchNames = $colorSet['french_names'] ?? '';

        return new ColorGameChoiceResponse(
            index: $index,
            codes: self::splitColors((string) $codes),
            names: self::splitColors((string) $names),
            frenchNames: self::splitColors((string) $frenchNames),
        );
    }

    /**
     * @return string[]
     */
    private static function splitColors(string $colors): array
    {
        if ('' === $colors) {
            return [];
        }

        return explode(self::COLORS_SEPARATOR, $colors);
    }

    private static function formatDate(string $date): string
    {
        return new \DateTimeImmutable($date, new \DateTimeZone('UTC'))->format(\DATE_ATOM);
    }
}

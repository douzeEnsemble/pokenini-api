<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\GameCountsResponse;
use App\DTO\Response\GameDailyCountResponse;
use App\DTO\Response\GameDexUsageResponse;
use App\DTO\Response\GameHardestPokemonResponse;
use App\DTO\Response\GameReportResponse;
use App\DTO\Response\GameRoundCountsResponse;
use App\DTO\Response\GameStatsResponse;
use App\DTO\Response\ReportDexResponse;
use App\DTO\Response\ReportPokemonResponse;

final class GameReportResponseFactory
{
    /** Every game is reported, in this order, even without any game played */
    public const array GAMES = ['color_game', 'shiny_game', 'which_shiny_game'];

    /**
     * @param array<array-key, array<array-key, mixed>> $gameCounts
     * @param array<array-key, array<array-key, mixed>> $dexUsage
     * @param array<array-key, array<array-key, mixed>> $hardestPokemons
     * @param array<array-key, array<array-key, mixed>> $dailyGames
     */
    public static function fromServiceArrays(
        array $gameCounts,
        array $dexUsage,
        array $hardestPokemons,
        array $dailyGames,
    ): GameReportResponse {
        $countsByGame = array_column($gameCounts, null, 'game');

        $games = [];
        foreach (self::GAMES as $game) {
            $counts = $countsByGame[$game] ?? [];

            $games[] = new GameStatsResponse(
                game: $game,
                games: self::fromCountsRow($counts),
                rounds: self::fromRoundCountsRow($counts),
                dexUsage: array_values(array_map(
                    self::fromDexUsageRow(...),
                    self::filterByGame($dexUsage, $game),
                )),
                hardestPokemons: array_values(array_map(
                    self::fromHardestPokemonRow(...),
                    self::filterByGame($hardestPokemons, $game),
                )),
            );
        }

        return new GameReportResponse(
            games: $games,
            dailyGames: array_map(self::fromDailyGamesRow(...), $dailyGames),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromCountsRow(array $row): GameCountsResponse
    {
        return new GameCountsResponse(
            total: self::int($row, 'games_count'),
            played: self::int($row, 'played_count'),
            inProgress: self::int($row, 'in_progress_count'),
            completed: self::int($row, 'completed_count'),
            abandoned: self::int($row, 'abandoned_count'),
            players: self::int($row, 'players_count'),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromRoundCountsRow(array $row): GameRoundCountsResponse
    {
        return new GameRoundCountsResponse(
            planned: self::int($row, 'round_count'),
            answered: self::int($row, 'answered_count'),
            correct: self::int($row, 'correct_count'),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromDexUsageRow(array $row): GameDexUsageResponse
    {
        return new GameDexUsageResponse(
            dex: new ReportDexResponse(
                slug: self::string($row, 'slug'),
                name: self::string($row, 'name'),
                frenchName: self::string($row, 'french_name'),
            ),
            games: self::int($row, 'games_count'),
            players: self::int($row, 'players_count'),
            answered: self::int($row, 'answered_count'),
            correct: self::int($row, 'correct_count'),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromHardestPokemonRow(array $row): GameHardestPokemonResponse
    {
        return new GameHardestPokemonResponse(
            pokemon: new ReportPokemonResponse(
                slug: self::string($row, 'slug'),
                name: self::string($row, 'name'),
                frenchName: self::string($row, 'french_name'),
                simplifiedName: self::string($row, 'simplified_name'),
                simplifiedFrenchName: self::string($row, 'simplified_french_name'),
                formsLabel: self::string($row, 'forms_label'),
                formsFrenchLabel: self::string($row, 'forms_french_label'),
                icon: self::string($row, 'icon'),
            ),
            answered: self::int($row, 'answered_count'),
            correct: self::int($row, 'correct_count'),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromDailyGamesRow(array $row): GameDailyCountResponse
    {
        return new GameDailyCountResponse(
            date: self::string($row, 'date'),
            colorGame: self::int($row, 'color_game'),
            shinyGame: self::int($row, 'shiny_game'),
            whichShinyGame: self::int($row, 'which_shiny_game'),
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return array<array-key, array<array-key, mixed>>
     */
    private static function filterByGame(array $rows, string $game): array
    {
        return array_filter($rows, static fn (array $row): bool => $game === $row['game']);
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private static function int(array $row, string $key): int
    {
        /** @var scalar $value */
        $value = $row[$key] ?? 0;

        return (int) $value;
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private static function string(array $row, string $key): string
    {
        /** @var scalar $value */
        $value = $row[$key];

        return (string) $value;
    }
}

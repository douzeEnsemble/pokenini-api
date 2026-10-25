<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class GameReportResponse
{
    /**
     * @param GameStatsResponse[]      $games
     * @param GameDailyCountResponse[] $dailyGames
     */
    public function __construct(
        public readonly array $games,
        #[SerializedName('daily_games')]
        public readonly array $dailyGames,
    ) {}
}

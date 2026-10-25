<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\GameReportRepository;

class GameReportService
{
    public const int HARDEST_POKEMONS_MIN_ANSWERS = 3;
    public const int HARDEST_POKEMONS_LIMIT = 10;
    public const int DAILY_GAMES_DAYS = 30;

    public function __construct(
        private readonly GameReportRepository $repository,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function getGameCounts(): array
    {
        return $this->repository->getGameCounts();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDexUsage(): array
    {
        return $this->repository->getDexUsage();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getHardestPokemons(): array
    {
        return $this->repository->getHardestPokemons(
            self::HARDEST_POKEMONS_MIN_ANSWERS,
            self::HARDEST_POKEMONS_LIMIT,
        );
    }

    /**
     * The last DAILY_GAMES_DAYS days, today included.
     *
     * @return list<array<string, mixed>>
     */
    public function getDailyGames(\DateTimeImmutable $today): array
    {
        return $this->repository->getDailyGames(
            $today->modify('-'.(self::DAILY_GAMES_DAYS - 1).' days'),
            $today,
        );
    }
}

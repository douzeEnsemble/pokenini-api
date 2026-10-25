<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\GameReportRepository;
use App\Service\GameReportService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameReportService::class)]
final class GameReportServiceTest extends TestCase
{
    #[Test]
    public function getGameCounts(): void
    {
        $rows = [['game' => 'color_game', 'games_count' => 3]];

        $repository = $this->createMock(GameReportRepository::class);
        $repository->expects($this->once())
            ->method('getGameCounts')
            ->willReturn($rows)
        ;

        $this->assertSame($rows, new GameReportService($repository)->getGameCounts());
    }

    #[Test]
    public function getDexUsage(): void
    {
        $rows = [['game' => 'color_game', 'slug' => 'demo']];

        $repository = $this->createMock(GameReportRepository::class);
        $repository->expects($this->once())
            ->method('getDexUsage')
            ->willReturn($rows)
        ;

        $this->assertSame($rows, new GameReportService($repository)->getDexUsage());
    }

    #[Test]
    public function getHardestPokemons(): void
    {
        $rows = [['game' => 'color_game', 'slug' => 'caterpie']];

        $repository = $this->createMock(GameReportRepository::class);
        $repository->expects($this->once())
            ->method('getHardestPokemons')
            ->with(3, 10)
            ->willReturn($rows)
        ;

        $this->assertSame($rows, new GameReportService($repository)->getHardestPokemons());
    }

    #[Test]
    public function getDailyGames(): void
    {
        $rows = [['date' => '2026-10-25', 'color_game' => 1]];

        $repository = $this->createMock(GameReportRepository::class);
        $repository->expects($this->once())
            ->method('getDailyGames')
            ->with(
                $this->callback(static fn (\DateTimeInterface $since): bool => '2026-09-26' === $since->format('Y-m-d')),
                $this->callback(static fn (\DateTimeInterface $until): bool => '2026-10-25' === $until->format('Y-m-d')),
            )
            ->willReturn($rows)
        ;

        $this->assertSame(
            $rows,
            new GameReportService($repository)->getDailyGames(new \DateTimeImmutable('2026-10-25')),
        );
    }
}

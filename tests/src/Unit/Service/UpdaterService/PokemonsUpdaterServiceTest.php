<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\PokemonsUpdaterService;
use App\Updater\PokemonsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonsUpdaterService::class)]
final class PokemonsUpdaterServiceTest extends TestCase
{
    #[Test]
    public function execute(): void
    {
        $service = $this->getService();

        $service->execute();
    }

    #[Test]
    public function getReport(): void
    {
        $service = $this->getService();

        $service->execute();
        $report = $service->getReport();

        $this->assertSame('p', $report->detail[0]->slug);
        $this->assertSame(0, $report->detail[0]->count);
    }

    private function getService(): PokemonsUpdaterService
    {
        $pokemonsUpdater = $this->createMock(PokemonsUpdater::class);
        $pokemonsUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $pokemonsUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('p'))
        ;

        return new PokemonsUpdaterService(
            $pokemonsUpdater
        );
    }
}

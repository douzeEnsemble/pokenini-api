<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\PokemonsUpdaterService;
use App\Updater\PokemonsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonsUpdaterService::class)]
final class PokemonsUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $service = $this->getService();

        $service->execute();
    }

    public function testGetReport(): void
    {
        $service = $this->getService();

        $service->execute();
        $service->getReport();
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

<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\Service\UpdaterService\PokemonsUpdaterService;
use App\Updater\PokemonsUpdater;
use PHPUnit\Framework\TestCase;
use App\DTO\DataChangeReport\Statistic;

class PokemonsUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
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

        $service = new PokemonsUpdaterService(
            $pokemonsUpdater
        );

        $service->execute();
    }
}

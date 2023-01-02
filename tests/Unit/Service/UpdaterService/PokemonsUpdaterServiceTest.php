<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\Service\UpdaterService\PokemonsUpdaterService;
use App\Updater\PokemonUpdater;
use PHPUnit\Framework\TestCase;
use App\DTO\DataChangeReport\Statistic;

class PokemonsUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $pokemonUpdater = $this->createMock(PokemonUpdater::class);
        $pokemonUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $pokemonUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('p'))
        ;

        $service = new PokemonsUpdaterService(
            $pokemonUpdater
        );

        $service->execute();
    }
}

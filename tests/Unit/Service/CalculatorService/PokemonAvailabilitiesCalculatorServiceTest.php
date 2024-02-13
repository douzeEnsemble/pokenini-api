<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\Calculator\PokemonAvailabilities\GameBundlesCalculator;
use App\Calculator\PokemonAvailabilities\GameBundlesShinyCalculator;
use App\DTO\DataChangeReport\Report;
use App\DTO\DataChangeReport\Statistic;
use App\Service\CalculatorService\PokemonAvailabilitiesCalculatorService;
use PHPUnit\Framework\TestCase;

class PokemonAvailabilitiesCalculatorServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $gameBundleStatistic = new Statistic('gb');
        $gameBundleShinyStatistic = new Statistic('gbs');

        $gameBundlesCalculator = $this->createMock(GameBundlesCalculator::class);
        $gameBundlesCalculator
            ->expects($this->once())
            ->method('execute')
        ;
        $gameBundlesCalculator
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn($gameBundleStatistic)
        ;

        $gameBundlesShinyCalculator = $this->createMock(GameBundlesShinyCalculator::class);
        $gameBundlesShinyCalculator
            ->expects($this->once())
            ->method('execute')
        ;
        $gameBundlesShinyCalculator
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn($gameBundleShinyStatistic)
        ;

        $service = new PokemonAvailabilitiesCalculatorService(
            $gameBundlesCalculator,
            $gameBundlesShinyCalculator,
        );

        $service->execute();

        $this->assertEquals(
            new Report([
                $gameBundleStatistic,
                $gameBundleShinyStatistic,
            ]),
            $service->getReport(),
        );
    }
}

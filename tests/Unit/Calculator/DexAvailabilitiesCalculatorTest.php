<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator;

use App\Calculator\DexAvailabilitiesCalculator;
use App\Calculator\DexAvailabilityCalculator;
use App\DTO\GameBundlesAvailabilities;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\Entity\Dex;
use App\Entity\Pokemon;
use App\Repository\DexAvailabilitiesRepository;
use App\Repository\DexRepository;
use App\Repository\PokemonsRepository;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DexAvailabilitiesCalculatorTest extends TestCase
{
    public function testExecute(): void
    {
        $dexAvailabilitiesRepository = $this->createMock(DexAvailabilitiesRepository::class);
        $dexAvailabilitiesRepository
            ->expects($this->once())
            ->method('removeAll')
        ;

        $dexQuery = $this->createMock(AbstractQuery::class);
        $dexQuery
            ->expects($this->once())
            ->method('toIterable')
            ->willReturn([
                new Dex(),
                new Dex(),
            ])
        ;
        $dexRepository = $this->createMock(DexRepository::class);
        $dexRepository
            ->expects($this->once())
            ->method('getQueryAll')
            ->willReturn($dexQuery)
        ;

        $dexAvailabilityCalculator = $this->createMock(DexAvailabilityCalculator::class);
        $dexAvailabilityCalculator
            ->expects($this->exactly(2))
            ->method('calculate')
            ->willReturn(3)
        ;

        $service = new DexAvailabilitiesCalculator(
            $dexAvailabilitiesRepository,
            $dexRepository,
            $dexAvailabilityCalculator,
        );

        $service->execute();
        $statistic = $service->getStatistic();

        $this->assertEquals(6, $statistic->count);
    }
}

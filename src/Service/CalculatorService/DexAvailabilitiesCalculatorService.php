<?php

declare(strict_types=1);

namespace App\Service\CalculatorService;

use App\Calculator\DexAvailabilitiesCalculator;
use App\DTO\DataChangeReport\Report;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DexAvailabilitiesCalculatorService extends AbstractCalculatorService
{
    public function __construct(
        private readonly DexAvailabilitiesCalculator $calculator,
        private readonly CacheInterface $cache
    ) {}

    #[\Override]
    public function execute(): void
    {
        /** @psalm-suppress UndefinedInterfaceMethod */
        $this->cache->clear();

        $this->calculator->execute();

        $this->report = new Report([
            $this->calculator->getStatistic(),
        ]);
    }
}

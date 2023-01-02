<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CalculatorService\DexAvailabilitiesCalculatorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:calculate:dex_availability')]
class CalculateDexAvailabilityCommand extends AbstractCalculateCommand
{
    protected static $defaultName = 'app:calculate:dex_availability';

    public function __construct(
        TranslatorInterface $translator,
        DexAvailabilitiesCalculatorService $calculatorService,
    ) {
        parent::__construct($translator, $calculatorService);
    }

    protected function configure(): void
    {
        $this
            ->setHelp("This command allows you to update dex availabilities")
        ;
    }
}

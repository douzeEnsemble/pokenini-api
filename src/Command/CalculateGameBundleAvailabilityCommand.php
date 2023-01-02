<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CalculatorService\GameBundleAvailabilitiesCalculatorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:calculate:game_bundle_availability')]
class CalculateGameBundleAvailabilityCommand extends AbstractCalculateCommand
{
    protected static $defaultName = 'app:calculate:game_bundle_availability';

    public function __construct(
        TranslatorInterface $translator,
        GameBundleAvailabilitiesCalculatorService $calculatorService,
    ) {
        parent::__construct($translator, $calculatorService);
    }

    protected function configure(): void
    {
        $this
            ->setHelp("This command allows you to update games' bundles' availabilities")
        ;
    }
}

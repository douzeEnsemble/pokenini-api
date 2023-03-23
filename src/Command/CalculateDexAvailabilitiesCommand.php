<?php

declare(strict_types=1);

namespace App\Command;

use App\ActionStarter\CalculateDexAvailabilitiesActionStarter;
use App\Service\CalculatorService\DexAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:calculate:dex_availabilities')]
class CalculateDexAvailabilitiesCommand extends AbstractCalculateCommand
{
    protected static $defaultName = 'app:calculate:dex_availabilities';

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        CalculateDexAvailabilitiesActionStarter $actionStarter,
        DexAvailabilitiesCalculatorService $calculatorService,
    ) {
        parent::__construct($translator, $entityManager, $actionStarter, $calculatorService);
    }

    protected function configure(): void
    {
        $this
            ->setHelp("This command allows you to update dex availabilities")
        ;
    }
}

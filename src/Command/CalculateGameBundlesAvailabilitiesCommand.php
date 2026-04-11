<?php

declare(strict_types=1);

namespace App\Command;

use App\ActionStarter\CalculateGameBundlesAvailabilitiesActionStarter;
use App\Service\CalculatorService\GameBundlesAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:calculate:game_bundles_availabilities')]
final class  CalculateGameBundlesAvailabilitiesCommand extends AbstractCalculateCommand
{
    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        CalculateGameBundlesAvailabilitiesActionStarter $actionStarter,
        GameBundlesAvailabilitiesCalculatorService $calculatorService,
    ) {
        parent::__construct($translator, $entityManager, $actionStarter, $calculatorService);
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setHelp("This command allows you to update game' bundles' availabilities")
        ;
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:calculate:game_bundles_availabilities';
    }
}

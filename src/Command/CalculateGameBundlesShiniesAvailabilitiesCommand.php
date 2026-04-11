<?php

declare(strict_types=1);

namespace App\Command;

use App\ActionStarter\CalculateGameBundlesShiniesAvailabilitiesActionStarter;
use App\Service\CalculatorService\GameBundlesShiniesAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:calculate:game_bundles_shinies_availabilities')]
final class  CalculateGameBundlesShiniesAvailabilitiesCommand extends AbstractCalculateCommand
{
    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        CalculateGameBundlesShiniesAvailabilitiesActionStarter $actionStarter,
        GameBundlesShiniesAvailabilitiesCalculatorService $calculatorService,
    ) {
        parent::__construct($translator, $entityManager, $actionStarter, $calculatorService);
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setHelp("This command allows you to update game' bundles' shinies' availabilities")
        ;
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:calculate:game_bundles_shinies_availabilities';
    }
}

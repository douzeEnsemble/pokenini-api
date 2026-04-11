<?php

declare(strict_types=1);

namespace App\Command;

use App\ActionStarter\UpdateGamesAvailabilitiesActionStarter;
use App\Service\UpdaterService\GamesAvailabilitiesUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:games_availabilities')]
final class   UpdateGamesAvailabilitiesCommand extends AbstractUpdateCommand
{
    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        UpdateGamesAvailabilitiesActionStarter $actionStarter,
        GamesAvailabilitiesUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $entityManager, $actionStarter, $updaterService);
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:update:games_availabilities';
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use App\ActionStarter\UpdateGamesShiniesAvailabilitiesActionStarter;
use App\Service\UpdaterService\GamesShiniesAvailabilitiesUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:games_shinies_availabilities')]
final class UpdateGamesShiniesAvailabilitiesCommand extends AbstractUpdateCommand
{
    protected static $defaultName = 'app:update:games_shinies_availabilities';

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        UpdateGamesShiniesAvailabilitiesActionStarter $actionStarter,
        GamesShiniesAvailabilitiesUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $entityManager, $actionStarter, $updaterService);
    }
}

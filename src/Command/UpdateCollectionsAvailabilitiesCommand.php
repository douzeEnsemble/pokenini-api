<?php

declare(strict_types=1);

namespace App\Command;

use App\ActionStarter\UpdateCollectionsAvailabilitiesActionStarter;
use App\Service\UpdaterService\CollectionsAvailabilitiesUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:collections_availabilities')]
final class   UpdateCollectionsAvailabilitiesCommand extends AbstractUpdateCommand
{
    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        UpdateCollectionsAvailabilitiesActionStarter $actionStarter,
        CollectionsAvailabilitiesUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $entityManager, $actionStarter, $updaterService);
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:update:collections_availabilities';
    }
}

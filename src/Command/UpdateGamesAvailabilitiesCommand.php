<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\UpdaterService\GamesAvailabilitiesUpdaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:games_availabilities')]
final class UpdateGamesAvailabilitiesCommand extends AbstractUpdateCommand
{
    protected static $defaultName = 'app:update:games_availabilities';

    public function __construct(
        TranslatorInterface $translator,
        GamesAvailabilitiesUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $updaterService);
    }
}

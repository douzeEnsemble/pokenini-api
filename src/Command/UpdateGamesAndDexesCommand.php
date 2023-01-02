<?php

namespace App\Command;

use App\Service\UpdaterService\GamesAndDexesUpdaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:games_and_dexes')]
final class UpdateGamesAndDexesCommand extends AbstractUpdateCommand
{
    protected static $defaultName = 'app:update:games_and_dexes';

    public function __construct(
        TranslatorInterface $translator,
        GamesAndDexesUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $updaterService);
    }
}

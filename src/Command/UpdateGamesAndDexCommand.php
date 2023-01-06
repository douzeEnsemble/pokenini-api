<?php

namespace App\Command;

use App\Service\UpdaterService\GamesAndDexUpdaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:games_and_dex')]
final class UpdateGamesAndDexCommand extends AbstractUpdateCommand
{
    protected static $defaultName = 'app:update:games_and_dex';

    public function __construct(
        TranslatorInterface $translator,
        GamesAndDexUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $updaterService);
    }
}

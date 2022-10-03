<?php

namespace App\Command;

use App\Service\UpdaterService\DexesUpdaterService;
use App\Service\UpdaterService\GamesUpdaterService;
use App\Service\UpdaterService\LabelsUpdaterService;
use App\Updater\PokemonUpdater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update:games_and_dexes')]
class GamesAndDexesUpdaterCommand extends Command
{
    protected static $defaultName = 'app:update:games_and_dexes';

    public function __construct(
        protected readonly GamesUpdaterService $gamesUpdaterService,
        protected readonly DexesUpdaterService $dexesUpdaterService
    ) {
        parent::__construct(self::$defaultName);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->gamesUpdaterService->execute();
        $this->dexesUpdaterService->execute();

        $output->writeln("<info>Games and dexes updated</info>");

        return Command::SUCCESS;
    }
}

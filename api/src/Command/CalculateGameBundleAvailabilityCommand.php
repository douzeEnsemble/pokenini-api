<?php

namespace App\Command;

use App\Repository\GameAvailabilityRepository;
use App\Repository\GameBundleAvailabilityRepository;
use App\Repository\GameBundleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:calculate:game_bundle_availability')]
class CalculateGameBundleAvailabilityCommand extends Command
{
    protected static $defaultName = 'app:calculate:game_bundle_availability';

    public function __construct(
        private GameBundleAvailabilityRepository $gameBundleAvailabilityRepository,
        private GameBundleRepository $gameBundleRepository,
        private GameAvailabilityRepository $gameAvailabilityRepository
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setHelp("This command allows you to update games' bundles' availabilities")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->gameBundleAvailabilityRepository->removeAll();

        $nb = $this->gameBundleAvailabilityRepository->calculate();

        $output->writeln("<info>$nb bundles' availabilities calculated</info>");

        return Command::SUCCESS;
    }
}

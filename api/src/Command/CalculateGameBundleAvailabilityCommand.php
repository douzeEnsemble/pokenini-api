<?php

namespace App\Command;

use App\Repository\GameBundleAvailabilityRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:calculate:game_bundle_availability')]
class CalculateGameBundleAvailabilityCommand extends Command
{
    protected static $defaultName = 'app:calculate:game_bundle_availability';

    public function __construct(
        private GameBundleAvailabilityRepository $gameBundleAvailabilityRepository
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setHelp("This command allows you to update games' bundles' availabilities")
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->gameBundleAvailabilityRepository->removeAll();

        $count = $this->gameBundleAvailabilityRepository->calculate();

        $output->writeln("<info>$count bundles' availabilities calculated</info>");

        return Command::SUCCESS;
    }
}

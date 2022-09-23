<?php

declare(strict_types=1);

namespace App\Command;

use App\Calculator\GameBundleAvailabilityCalculator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(name: 'app:calculate:game_bundle_availability')]
class CalculateGameBundleAvailabilityCommand extends Command
{
    protected static $defaultName = 'app:calculate:game_bundle_availability';

    public function __construct(
        private GameBundleAvailabilityCalculator $gameBundleAvailabilityCalculator,
        private readonly CacheInterface $cache,
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
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->cache->clear();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->gameBundleAvailabilityCalculator->execute();

        $output->writeln("<info>$count bundles' availabilities calculated</info>");

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use App\Calculator\DexAvailabilityCalculator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(name: 'app:calculate:dex_availability')]
class CalculateDexAvailabilityCommand extends Command
{
    protected static $defaultName = 'app:calculate:dex_availability';

    public function __construct(
        private readonly DexAvailabilityCalculator $dexAvailabilityCalculator,
        private readonly CacheInterface $cache,
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setHelp("This command allows you to update dex availabilities")
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
        $nbDexAvailabilities = $this->dexAvailabilityCalculator->execute();

        $output->writeln("<info>$nbDexAvailabilities dex' availabilities calculated</info>");

        return Command::SUCCESS;
    }
}

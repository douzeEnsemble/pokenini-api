<?php

declare(strict_types=1);

namespace App\Command;

use App\Updater\RegionalDexNumberUpdater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update:regional_dex_number')]
class RegionalDexNumberUpdaterCommand extends Command
{
    protected static $defaultName = 'app:update:regional_dex_number';

    public function __construct(
        protected readonly RegionalDexNumberUpdater $updater
    ) {
        parent::__construct(self::$defaultName);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->updater->execute();
        $count = $this->updater->getCount();

        $output->writeln("<info>$count regional dex numbers updated</info>");

        return Command::SUCCESS;
    }
}

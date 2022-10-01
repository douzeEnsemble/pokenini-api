<?php

declare(strict_types=1);

namespace App\Command;

use App\Updater\PokemonUpdater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update:pokemon')]
class PokemonUpdaterCommand extends Command
{
    protected static $defaultName = 'app:update:pokemon';

    public function __construct(
        protected readonly PokemonUpdater $updater
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

        $output->writeln("<info>$count pokémons updated</info>");

        return Command::SUCCESS;
    }
}

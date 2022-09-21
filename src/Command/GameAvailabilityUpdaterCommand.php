<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Updater\GameAvailabilityUpdater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update:game_availability')]
class GameAvailabilityUpdaterCommand extends Command
{
    protected static $defaultName = 'app:update:game_availability';

    public function __construct(
        protected readonly GameAvailabilityUpdater $updater
    ) {
        parent::__construct(self::$defaultName);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->updater->execute();

        return Command::SUCCESS;
    }
}

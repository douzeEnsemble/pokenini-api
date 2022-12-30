<?php

namespace App\Command;

use App\Service\UpdaterService\LabelsUpdaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update:labels')]
class UpdateLabelsCommand extends Command
{
    protected static $defaultName = 'app:update:labels';

    public function __construct(
        protected readonly LabelsUpdaterService $updaterService
    ) {
        parent::__construct(self::$defaultName);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->updaterService->execute();

        $output->writeln("<info>Labels updated</info>");

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use App\ActionEnder\ActionEnderTrait;
use App\ActionStarter\ActionStarterInterface;
use App\Service\UpdaterService\UpdaterServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractUpdateCommand extends Command
{
    use ActionEnderTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        protected readonly ActionStarterInterface $actionStarter,
        protected readonly UpdaterServiceInterface $updaterService
    ) {
        parent::__construct($this->getCommandName());
    }

    abstract protected function getCommandName(): string;

    #[\Override]
    protected function configure(): void
    {
        $this->addOption('sheet-name', null, InputOption::VALUE_OPTIONAL, 'The sheet name to use');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = $this->actionStarter->start();

        $rawSheetName = $input->getOption('sheet-name');
        $sheetName = is_string($rawSheetName) ? $rawSheetName : null;

        try {
            $this->updaterService->execute($sheetName);

            $report = $this->updaterService->getReport();

            $this->endActionLog($message, $report);
        } catch (\Exception $e) {
            $this->endInErrorActionLog($message, $e->getMessage());

            $output->writeln("<error>{$e->getMessage()}</error>");

            return Command::FAILURE;
        }

        foreach ($report->detail as $statistic) {
            $label = $this->translator->trans("update.{$statistic->slug}");

            $output->writeln("<info>{$statistic->count} {$label} updated</info>");
        }

        return Command::SUCCESS;
    }
}

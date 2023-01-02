<?php

declare(strict_types=1);

namespace App\Command;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\UpdaterServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractUpdateCommand extends Command
{
    protected static $defaultName;

    public function __construct(
        private readonly TranslatorInterface $translator,
        protected readonly UpdaterServiceInterface $updaterService
    ) {
        parent::__construct(self::$defaultName);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->updaterService->execute();

        $report = $this->updaterService->getReport();

        /** @var Statistic $statistic */
        foreach ($report->detail as $statistic) {
            $label = $this->translator->trans("update.{$statistic->slug}");

            $output->writeln("<info>{$statistic->count} $label updated</info>");
        }

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use App\DTO\DataChangeReport\Statistic;
use App\Service\CalculatorService\CalculatorServiceInterface as CalculatorServiceCalculatorServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractCalculateCommand extends Command
{
    protected static $defaultName;

    public function __construct(
        private readonly TranslatorInterface $translator,
        protected readonly CalculatorServiceCalculatorServiceInterface $updaterService
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
            $label = $this->translator->trans("calculate.{$statistic->slug}");

            $output->writeln("<info>{$statistic->count} $label calculated</info>");
        }

        return Command::SUCCESS;
    }
}

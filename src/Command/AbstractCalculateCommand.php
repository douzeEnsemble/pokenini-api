<?php

declare(strict_types=1);

namespace App\Command;

use App\ActionEnder\ActionEnderTrait;
use App\ActionStarter\ActionStarterInterface;
use App\DTO\DataChangeReport\Statistic;
use App\Service\CalculatorService\CalculatorServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractCalculateCommand extends Command
{
    use ActionEnderTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        protected readonly ActionStarterInterface $actionStarter,
        protected readonly CalculatorServiceInterface $calculatorService,
    ) {
        parent::__construct($this->getCommandName());
    }

    abstract protected function getCommandName(): string;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = $this->actionStarter->start();

        try {
            $this->calculatorService->execute();

            $report = $this->calculatorService->getReport();

            $this->endActionLog($message, $report);
        } catch (\Exception $e) {
            $this->endInErrorActionLog($message, $e->getMessage());

            $output->writeln("<error>{$e->getMessage()}</error>");

            return Command::FAILURE;
        }

        /** @var Statistic $statistic */
        foreach ($report->detail as $statistic) {
            $label = $this->translator->trans("calculate.{$statistic->slug}");

            $output->writeln("<info>{$statistic->count} {$label} calculated</info>");
        }

        return Command::SUCCESS;
    }
}

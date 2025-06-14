<?php

namespace App\Tests\Unit\Command;

use App\ActionStarter\CalculateDexAvailabilitiesActionStarter;
use App\Command\CalculateDexAvailabilitiesCommand;
use App\Entity\ActionLog;
use App\Repository\ActionLogsRepository;
use App\Service\CalculatorService\DexAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(CalculateDexAvailabilitiesCommand::class)]
class CalculateDexAvailabilitiesCommandTest extends TestCase
{
    public function testFailureOnException(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);

        $actionLog = new ActionLog('CalculateDexAvailabilities');

        $repository = $this->createMock(ActionLogsRepository::class);
        $repository
            ->expects($this->once())
            ->method('find')
            ->with('')
            ->willReturn($actionLog)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository)
        ;
        $entityManager
            ->expects($this->once())
            ->method('persist')
        ;
        $entityManager
            ->expects($this->exactly(2))
            ->method('flush')
        ;

        $actionStarter = new CalculateDexAvailabilitiesActionStarter($entityManager);

        $calculatorService = $this->createMock(DexAvailabilitiesCalculatorService::class);
        $calculatorService
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Oh zut'))
        ;

        $command = new CalculateDexAvailabilitiesCommand(
            $translator,
            $entityManager,
            $actionStarter,
            $calculatorService,
        );

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $output
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>Oh zut</error>')
        ;

        $command->run($input, $output);

        $this->assertEquals($actionLog->errorTrace, 'Oh zut');
        $this->assertNotNull($actionLog->doneAt);
    }
}

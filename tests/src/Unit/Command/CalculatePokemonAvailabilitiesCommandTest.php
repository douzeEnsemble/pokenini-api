<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\ActionStarter\CalculatePokemonAvailabilitiesActionStarter;
use App\Command\CalculatePokemonAvailabilitiesCommand;
use App\Entity\ActionLog;
use App\Repository\ActionLogsRepository;
use App\Service\CalculatorService\PokemonAvailabilitiesCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(CalculatePokemonAvailabilitiesCommand::class)]
final class CalculatePokemonAvailabilitiesCommandTest extends TestCase
{
    #[Test]
    public function failureOnException(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->never())
            ->method('trans')
        ;

        $actionLog = new ActionLog('CalculatePokemonAvailabilities');

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

        $actionStarter = new CalculatePokemonAvailabilitiesActionStarter($entityManager);

        $calculatorService = $this->createMock(PokemonAvailabilitiesCalculatorService::class);
        $calculatorService
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Oh zut'))
        ;

        $command = new CalculatePokemonAvailabilitiesCommand(
            $translator,
            $entityManager,
            $actionStarter,
            $calculatorService,
        );

        $input = $this->createStub(InputInterface::class);
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

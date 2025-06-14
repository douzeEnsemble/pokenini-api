<?php

namespace App\Tests\Unit\Command;

use App\ActionStarter\UpdatePokemonsActionStarter;
use App\Command\UpdatePokemonsCommand;
use App\Entity\ActionLog;
use App\Repository\ActionLogsRepository;
use App\Service\UpdaterService\PokemonsUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(UpdatePokemonsCommand::class)]
class UpdatePokemonsCommandTest extends TestCase
{
    public function testFailureOnException(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);

        $actionLog = new ActionLog('UpdatePokemons');

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

        $actionStarter = new UpdatePokemonsActionStarter($entityManager);

        $updaterService = $this->createMock(PokemonsUpdaterService::class);
        $updaterService
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Oh zut'))
        ;

        $command = new UpdatePokemonsCommand(
            $translator,
            $entityManager,
            $actionStarter,
            $updaterService,
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

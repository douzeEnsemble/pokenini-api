<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\ActionStarter\UpdateGamesAvailabilitiesActionStarter;
use App\Command\UpdateGamesAvailabilitiesCommand;
use App\Entity\ActionLog;
use App\Repository\ActionLogsRepository;
use App\Service\UpdaterService\GamesAvailabilitiesUpdaterService;
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
#[CoversClass(UpdateGamesAvailabilitiesCommand::class)]
final class UpdateGamesAvailabilitiesCommandTest extends TestCase
{
    #[Test]
    public function failureOnException(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->never())
            ->method('trans')
        ;

        $actionLog = new ActionLog('UpdateGamesAvailabilities');

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

        $actionStarter = new UpdateGamesAvailabilitiesActionStarter($entityManager);

        $updaterService = $this->createMock(GamesAvailabilitiesUpdaterService::class);
        $updaterService
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Oh zut'))
        ;

        $command = new UpdateGamesAvailabilitiesCommand(
            $translator,
            $entityManager,
            $actionStarter,
            $updaterService,
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

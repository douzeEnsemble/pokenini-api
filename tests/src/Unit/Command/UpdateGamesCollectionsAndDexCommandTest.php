<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\ActionStarter\UpdateGamesCollectionsAndDexActionStarter;
use App\Command\UpdateGamesCollectionsAndDexCommand;
use App\Entity\ActionLog;
use App\Repository\ActionLogsRepository;
use App\Service\UpdaterService\GamesCollectionsAndDexUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(UpdateGamesCollectionsAndDexCommand::class)]
final class UpdateGamesCollectionsAndDexCommandTest extends TestCase
{
    public function testFailureOnException(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->never())
            ->method('trans')
        ;

        $actionLog = new ActionLog('UpdateGamesCollectionsAndDex');

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

        $actionStarter = new UpdateGamesCollectionsAndDexActionStarter($entityManager);

        $updaterService = $this->createMock(GamesCollectionsAndDexUpdaterService::class);
        $updaterService
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Oh zut'))
        ;

        $command = new UpdateGamesCollectionsAndDexCommand(
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

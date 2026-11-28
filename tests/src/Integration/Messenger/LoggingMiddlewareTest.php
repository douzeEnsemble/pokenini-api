<?php

declare(strict_types=1);

namespace App\Tests\Integration\Messenger;

use App\Message\CalculatePokemonAvailabilities;
use App\Tests\Common\Traits\GetterTrait\GetActionLogTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

/**
 * Checks the messenger configuration: each handled message is logged with its duration and memory.
 *
 * @internal
 */
#[CoversNothing]
final class LoggingMiddlewareTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use InteractsWithMessenger;
    use GetActionLogTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function logsTheDurationAndMemoryOfAHandledMessage(): void
    {
        $handler = new TestHandler();
        $logger = self::getContainer()->get('monolog.logger.messenger');
        $this->assertInstanceOf(Logger::class, $logger);
        $logger->pushHandler($handler);

        $transport = $this->transport('async');
        $transport->send(new CalculatePokemonAvailabilities($this->getIdToProcess(CalculatePokemonAvailabilities::class)));
        $transport->process(1);

        $records = array_values(array_filter(
            $handler->getRecords(),
            static fn (LogRecord $record): bool => '"{class}" message successfully handled.' === $record->message,
        ));

        $this->assertCount(1, $records);
        $this->assertSame(CalculatePokemonAvailabilities::class, $records[0]->context['class']);
        $this->assertArrayHasKey('duration_ms', $records[0]->context);
        $this->assertArrayHasKey('memory_usage', $records[0]->context);
    }
}

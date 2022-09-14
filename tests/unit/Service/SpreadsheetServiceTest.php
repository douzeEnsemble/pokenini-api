<?php

namespace unit\Service;

use App\Service\SpreadsheetService;
use Google\Client;
use Google\Service\Sheets\ValueRange;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class SpreadsheetServiceTest extends TestCase
{
    public function testGet(): void
    {
        $logger = new NullLogger();
        $client = $this->createMock(Client::class);
        $client
            ->method('getLogger')
            ->willReturn($logger)
        ;
        $client
            ->method('shouldDefer')
            ->willReturn(false)
        ;

        $valueRange = new ValueRange();
        $client
            ->expects($this->once())
            ->method('execute')
            ->willReturn($valueRange)
        ;

        $cache = new ArrayAdapter();

        $service = new SpreadsheetService($client, $cache);

        $service->get('azertyuiop', 'A1:R12');
        $service->get('azertyuiop', 'A1:R12');
    }
}

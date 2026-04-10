<?php

declare(strict_types=1);

namespace App\Tests\Unit\Updater;

use App\Service\SpreadsheetService;
use App\Updater\DexUpdater;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\Sheets\ValueRange;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
#[CoversClass(DexUpdater::class)]
final class DexUpdaterTest extends TestCase
{
    public function testExecute(): void
    {
        $updater = $this->getService();

        $updater->execute('douze');
    }

    public function testStatistic(): void
    {
        $updater = $this->getService();

        $updater->execute('douze');

        $statistic = $updater->getStatistic();

        $this->assertSame('dex', $statistic->slug);
        $this->assertSame(1, $statistic->count);
    }

    private function getService(): DexUpdater
    {
        $headerRange = new ValueRange();
        $headerRange->values = [$this->getHeader()];

        $recordRange = new ValueRange();
        $recordRange->values = [$this->getRecord()];

        $spreadsheetService = $this->createMock(SpreadsheetService::class);
        $spreadsheetService
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn(
                $headerRange,
                $recordRange,
            )
        ;

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->exactly(2))
            ->method('executeStatement')
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($connection)
        ;

        $logger = $this->createMock(LoggerInterface::class);

        return new DexUpdater(
            $spreadsheetService,
            $entityManager,
            $logger,
            '12Douze12'
        );
    }

    /**
     * @return string[]
     */
    private function getHeader(): array
    {
        return [
            'Slug',
            'Name',
            'French Name',
            'Order',
            'Election Order',
            'Selection rule',
            'Is Shiny',
            'Is Premium',
            'Is Display Form',
            'Can Hold Election',
            'Is released',
            'Display template',
            '#Region',
            'French description',
            'Description',
            'Banner',
        ];
    }

    /**
     * @return bool[]|string[]
     */
    private function getRecord(): array
    {
        return [
            'demo',
            'Demo',
            'Démo',
            '1',
            '10',
            'TRUE',
            false,
            false,
            false,
            false,
            false,
            'box',
            '',
            '',
            '',
            '',
        ];
    }
}

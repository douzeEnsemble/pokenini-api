<?php

declare(strict_types=1);

namespace unit\Updater;

use App\Exception\InvalidSheetDataException;
use App\Service\SpreadsheetService;
use App\Updater\CatchStatesUpdater;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Sheets\ValueRange;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CatchStateUpdaterTest extends TestCase
{
    public function testGettingSpreasheetLog(): void
    {
        $exception = new GoogleServiceException('Something bad happenned');

        $spreadsheetService = $this->createMock(SpreadsheetService::class);
        $spreadsheetService
            ->expects($this->once())
            ->method('get')
            ->will($this->throwException($exception))
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                "Can't get data for range 'douze'!A1:E1",
                [
                    'exception' => $exception,
                ]
            )
        ;

        $updater = new CatchStatesUpdater(
            $spreadsheetService,
            $entityManager,
            $logger,
            '12Douze12'
        );

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage("Can't get data for range 'douze'!A1:E1");

        $updater->execute('douze');
    }

    public function testGettingEmptyHeaderLog(): void
    {
        $headerRange = new ValueRange();
        $headerRange->values = [];

        $spreadsheetService = $this->createMock(SpreadsheetService::class);
        $spreadsheetService
            ->expects($this->once())
            ->method('get')
            ->willReturn($headerRange)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Spreadsheet is empty',
                [
                    'spreadsheet' => "'douze'!A1:E1",
                ]
            )
        ;

        $updater = new CatchStatesUpdater(
            $spreadsheetService,
            $entityManager,
            $logger,
            '12Douze12'
        );

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage('Spreadsheet is empty');

        $updater->execute('douze');
    }

    public function testGettingInvalidHeaderLog(): void
    {
        $headerRange = new ValueRange();
        $headerRange->values = [
            [
                'a',
                'b',
            ]
        ];

        $spreadsheetService = $this->createMock(SpreadsheetService::class);
        $spreadsheetService
            ->expects($this->once())
            ->method('get')
            ->willReturn($headerRange)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'This is not a valid data spreadsheet',
                [
                    'header' => [
                        'a',
                        'b',
                    ],
                    'expectedHeader' => [
                        'Color',
                        'French Name',
                        'Name',
                        'Order',
                        'Slug',
                    ],
                ]
            )
        ;

        $updater = new CatchStatesUpdater(
            $spreadsheetService,
            $entityManager,
            $logger,
            '12Douze12'
        );

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage('This is not a valid data spreadsheet');

        $updater->execute('douze');
    }

    public function testGettingEmptyRecordLog(): void
    {
        $headerRange = new ValueRange();
        $headerRange->values = [
            [
                'Color',
                'French Name',
                'Name',
                'Order',
                'Slug',
            ]
        ];

        $recordRange = new ValueRange();
        $recordRange->values = [];

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

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection)
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'There is not data in spreadsheet',
                [
                    'spreadsheet' => "'douze'!A2:E",
                ]
            )
        ;

        $updater = new CatchStatesUpdater(
            $spreadsheetService,
            $entityManager,
            $logger,
            '12Douze12'
        );

        $this->expectException(InvalidSheetDataException::class);
        $this->expectExceptionMessage('There is not data in spreadsheet');

        $updater->execute('douze');
    }
}

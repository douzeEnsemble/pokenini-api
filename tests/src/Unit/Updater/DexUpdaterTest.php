<?php

declare(strict_types=1);

namespace App\Tests\Unit\Updater;

use App\Service\SpreadsheetService;
use App\Updater\DexUpdater;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\Sheets\ValueRange;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
#[CoversClass(DexUpdater::class)]
final class DexUpdaterTest extends TestCase
{
    #[Test]
    public function execute(): void
    {
        $updater = $this->getService();

        $updater->execute('douze');
    }

    #[Test]
    public function statistic(): void
    {
        $updater = $this->getService();

        $updater->execute('douze');

        $statistic = $updater->getStatistic();

        $this->assertSame('dex', $statistic->slug);
        $this->assertSame(1, $statistic->count);
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    #[Test]
    #[DataProvider('providerCanBeColorPlay')]
    public function canBeColorPlay(string $value, int $expected): void
    {
        $parameters = [];

        $updater = $this->getService(
            $value,
            static function (string $sql, array $params) use (&$parameters): int {
                $parameters = $params;

                return 1;
            },
        );

        $updater->execute('douze');

        $this->assertArrayHasKey('can_be_color_play', $parameters);
        $this->assertSame($expected, $parameters['can_be_color_play']);
    }

    /**
     * @return array<string, array{string, int}>
     */
    public static function providerCanBeColorPlay(): array
    {
        return [
            'true' => ['TRUE', 1],
            'false' => ['FALSE', 0],
            'empty' => ['', 0],
        ];
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    #[Test]
    public function gameOrders(): void
    {
        $parameters = [];

        $updater = $this->getService(
            'TRUE',
            static function (string $sql, array $params) use (&$parameters): int {
                $parameters = $params;

                return 1;
            },
            ['12', '', '3'],
        );

        $updater->execute('douze');

        $this->assertArrayHasKey('color_game_order_number', $parameters);
        $this->assertSame(12, $parameters['color_game_order_number']);
        $this->assertArrayHasKey('shiny_game_order_number', $parameters);
        $this->assertSame(0, $parameters['shiny_game_order_number']);
        $this->assertArrayHasKey('which_shiny_game_order_number', $parameters);
        $this->assertSame(3, $parameters['which_shiny_game_order_number']);
    }

    /**
     * @param string[] $gameOrders
     */
    private function getService(
        string $canBeColorPlay = 'TRUE',
        ?\Closure $executeStatement = null,
        array $gameOrders = ['1', '2', '3'],
    ): DexUpdater {
        $headerRange = new ValueRange();
        $headerRange->values = [$this->getHeader()];

        $recordRange = new ValueRange();
        $recordRange->values = [$this->getRecord($canBeColorPlay, $gameOrders)];

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
            ->willReturnCallback($executeStatement ?? static fn (): int => 1)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($connection)
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->never())
            ->method('error')
        ;

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
            'Can be Color Play',
            'Is released',
            'Display template',
            '#Region',
            'French description',
            'Description',
            'Banner',
            'Banner Layers',
            'Color Game Order',
            'Shiny Game Order',
            'Which Shiny Game Order',
        ];
    }

    /**
     * @param string[] $gameOrders
     *
     * @return bool[]|string[]
     */
    private function getRecord(string $canBeColorPlay = 'TRUE', array $gameOrders = ['1', '2', '3']): array
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
            $canBeColorPlay,
            false,
            'box',
            '',
            '',
            '',
            '',
            'shiny,mega',
            ...$gameOrders,
        ];
    }
}

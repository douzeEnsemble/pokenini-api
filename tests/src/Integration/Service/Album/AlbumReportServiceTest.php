<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Album;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumReport\Report;
use App\Service\Album\AlbumReportService;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(AlbumReportService::class)]
class AlbumReportServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[DataProvider('providerGetReport')]
    public function testGetReport(
        string $trainerId,
        string $dexSlug,
        int $countNo,
        int $countMaybe,
        int $countMaybeNot,
        int $countYes,
        int $countTotal
    ): void {
        /** @var AlbumReportService $service */
        $service = static::getContainer()->get(AlbumReportService::class);

        $report = $service->get($trainerId, $dexSlug, AlbumFilters::createFromArray([]));
        $this->assertReport($report, $countNo, $countMaybe, $countMaybeNot, $countYes, $countTotal);
    }

    /**
     * @return int[][]|string[][]
     */
    public static function providerGetReport(): array
    {
        return [
            '7b5_redgreenblueyellow' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'redgreenblueyellow',
                4,
                1,
                2,
                0,
                7,
            ],
            '7b5_goldsilvercrystal' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'goldsilvercrystal',
                8,
                0,
                0,
                1,
                9,
            ],
            'bd3_redgreenblueyellow' => [
                'bd307a3ec329e10a2cff8fb87480823da114f8f4',
                'redgreenblueyellow',
                6,
                0,
                0,
                1,
                7,
            ],
            '465_redgreenblueyellow' => [
                '46546542313186',
                'redgreenblueyellow',
                0,
                0,
                0,
                0,
                7,
            ],
            '7b5_home' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                9,
                3,
                3,
                7,
                22,
            ],
            '7b5_homeshiny' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home_shiny',
                11,
                0,
                0,
                0,
                11,
            ],
        ];
    }

    /**
     * @param string[][] $filters
     */
    #[DataProvider('providerGetReportFiltered')]
    public function testGetReportFiltered(
        string $trainerId,
        string $dexSlug,
        array $filters,
        int $countNo,
        int $countMaybe,
        int $countMaybeNot,
        int $countYes,
        int $countTotal
    ): void {
        /** @var AlbumReportService $service */
        $service = static::getContainer()->get(AlbumReportService::class);

        $report = $service->get($trainerId, $dexSlug, AlbumFilters::createFromArray($filters));
        $this->assertReport($report, $countNo, $countMaybe, $countMaybeNot, $countYes, $countTotal);
    }

    /**
     * @return int[][]|string[][]|string[][][][]
     */
    public static function providerGetReportFiltered(): array
    {
        return array_merge(
            AlbumReportServiceData::getTypesReportFilteredProvider(),
            AlbumReportServiceData::getFormsReportFilteredProvider(),
            AlbumReportServiceData::getCatchStatesReportFilteredProvider(),
            AlbumReportServiceData::getOriginalGamesReportFilteredProvider(),
            AlbumReportServiceData::getGamesBundlesReportFilteredProvider(),
            AlbumReportServiceData::getFamiliesReportFilteredProvider(),
            AlbumReportServiceData::getCollectionsReportFilteredProvider(),
        );
    }

    private function assertReport(
        Report $report,
        int $countNo,
        int $countMaybe,
        int $countMaybeNot,
        int $countYes,
        int $countTotal
    ): void {
        $details = [];
        foreach ($report->detail as $detail) {
            $details[$detail->slug] = $detail->count;
        }

        $this->assertEquals(
            [
                'no' => $countNo,
                'maybe' => $countMaybe,
                'maybenot' => $countMaybeNot,
                'yes' => $countYes,
            ],
            $details,
        );

        $this->assertEquals($countTotal, $report->total);
        $this->assertEquals($countYes, $report->totalCaught);
        $this->assertEquals($countTotal - $countMaybe - $countMaybeNot - $countYes, $report->totalUncaught);
    }
}

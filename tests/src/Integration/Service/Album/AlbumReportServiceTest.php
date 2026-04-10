<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service\Album;

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
final class AlbumReportServiceTest extends KernelTestCase
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
        int $countTotal,
    ): void {
        /** @var AlbumReportService $service */
        $service = static::getContainer()->get(AlbumReportService::class);

        $report = $service->get($trainerId, $dexSlug, AlbumFilters::createFromArray([]));
        $this->assertReport($report, $countNo, $countMaybe, $countMaybeNot, $countYes, $countTotal);
    }

    /**
     * @return array<string, array{
     *  trainerId: string,
     *  dexSlug: string,
     *  countNo: int,
     *  countMaybe: int,
     *  countMaybeNot: int,
     *  countYes: int,
     *  countTotal: int,
     * }>
     */
    public static function providerGetReport(): array
    {
        return [
            '7b5_redgreenblueyellow' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'redgreenblueyellow',
                'countNo' => 4,
                'countMaybe' => 1,
                'countMaybeNot' => 2,
                'countYes' => 0,
                'countTotal' => 7,
            ],
            '7b5_goldsilvercrystal' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'goldsilvercrystal',
                'countNo' => 8,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 1,
                'countTotal' => 9,
            ],
            'bd3_redgreenblueyellow' => [
                'trainerId' => 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
                'dexSlug' => 'redgreenblueyellow',
                'countNo' => 6,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 1,
                'countTotal' => 7,
            ],
            '465_redgreenblueyellow' => [
                'trainerId' => '46546542313186',
                'dexSlug' => 'redgreenblueyellow',
                'countNo' => 0,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 7,
            ],
            '7b5_home' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home',
                'countNo' => 9,
                'countMaybe' => 3,
                'countMaybeNot' => 3,
                'countYes' => 7,
                'countTotal' => 22,
            ],
            '7b5_homeshiny' => [
                'trainerId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dexSlug' => 'home_shiny',
                'countNo' => 11,
                'countMaybe' => 0,
                'countMaybeNot' => 0,
                'countYes' => 0,
                'countTotal' => 11,
            ],
        ];
    }

    /**
     * @param array<string, array<int, string>> $filters
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
     * @return array<string, array{
     *  trainerId: string,
     *  dexSlug: string,
     *  filters: array<string, array<int, string>>,
     *  countNo: int,
     *  countMaybe: int,
     *  countMaybeNot: int,
     *  countYes: int,
     *  countTotal: int,
     * }>
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

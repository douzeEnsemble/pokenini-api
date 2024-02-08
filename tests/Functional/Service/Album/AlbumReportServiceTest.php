<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Album;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumReport\Report;
use App\Service\Album\AlbumReportService;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AlbumReportServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @dataProvider getReportProvider
     */
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
     * @param string[][] $filters
     *
     * @dataProvider getReportFilteredProvider
     */
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
     * @return string[][]|int[][]
     */
    public function getReportProvider(): array
    {
        return [
            [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'redgreenblueyellow',
                1,
                1,
                2,
                0,
                7,
            ],
            [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'goldsilvercrystal',
                2,
                0,
                0,
                1,
                9,
            ],
            [
                'bd307a3ec329e10a2cff8fb87480823da114f8f4',
                'redgreenblueyellow',
                0,
                0,
                0,
                1,
                7,
            ],
            [
                '46546542313186',
                'redgreenblueyellow',
                0,
                0,
                0,
                0,
                7,
            ],
            [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                8,
                3,
                3,
                7,
                22,
            ],
            [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home_shiny',
                0,
                0,
                0,
                0,
                11,
            ]
        ];
    }

    /**
     * @return string[][]|string[][][][]|int[][]
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getReportFilteredProvider(): array
    {
        return [
            'primary_type' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'primaryTypes' => [
                        'grass',
                    ],
                ],
                6,
                0,
                0,
                0,
                6,
            ],
            'secondary_type' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'secondaryTypes' => [
                        'normal',
                    ],
                ],
                1,
                0,
                2,
                0,
                3,
            ],
            'primary_and_secondary_types' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'primaryTypes' => [
                        'bug',
                    ],
                    'secondaryTypes' => [
                        'flying',
                    ],
                ],
                1,
                0,
                0,
                2,
                3,
            ],
            'category_form' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'categoryForms' => [
                        'starter',
                    ],
                ],
                1,
                0,
                0,
                1,
                2,
            ],
            'regional_form' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'regionalForms' => [
                        'alolan',
                    ],
                ],
                1,
                0,
                2,
                0,
                3,
            ],
            'special_form' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'specialForms' => [
                        'gigantamax',
                    ],
                ],
                2,
                0,
                0,
                0,
                2,
            ],
            'special_forms' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'specialForms' => [
                        'gigantamax',
                        'mega',
                    ],
                ],
                3,
                0,
                0,
                0,
                3,
            ],
            'variant_form' => [
                '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'home',
                [
                    'variantForms' => [
                        'gender',
                    ],
                ],
                1,
                2,
                0,
                1,
                4,
            ],
        ];
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

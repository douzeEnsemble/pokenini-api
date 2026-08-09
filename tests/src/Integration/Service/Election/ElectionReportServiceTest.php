<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service\Election;

use App\DTO\DexQueryOptions;
use App\Service\Election\ElectionReportService;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(ElectionReportService::class)]
final class ElectionReportServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string TRAINER_U12 = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function getReturnsTopAndMetricsForDemoDex(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $report = $service->get(self::TRAINER_U12, 'demo', '', 5);

        $this->assertCount(5, $report->top);
        $this->assertEquals(
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
                'under_max_view_count' => 15,
                'max_view_count' => 15,
                'dex_total_count' => 21,
            ],
            $report->metrics,
        );
    }

    #[Test]
    public function getReturnsTopAndMetricsForAffineeElection(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $report = $service->get(self::TRAINER_U12, 'redgreenblueyellow', 'affinee', 5);

        $this->assertEquals(
            [
                'view_count_sum' => 9,
                'win_count_sum' => 6,
                'view_count_max' => 3,
                'win_count_max' => 3,
                'under_max_view_count' => 1,
                'max_view_count' => 1,
                'dex_total_count' => 7,
            ],
            $report->metrics,
        );
    }

    #[Test]
    public function getReturnsZeroedMetricsForUnknownElectionSlug(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $report = $service->get(self::TRAINER_U12, 'redgreenblueyellow', 'doesntexists', 5);

        $this->assertEquals(
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
                'under_max_view_count' => 7,
                'max_view_count' => 0,
                'dex_total_count' => 7,
            ],
            $report->metrics,
        );
    }

    #[Test]
    public function getEligibleDexDefaultsToReleasedNonPremium(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $rows = $service->getEligibleDex(new DexQueryOptions());

        $slugs = array_map(static function (array $row): string {
            /** @var scalar $slug */
            $slug = $row['slug'];

            return (string) $slug;
        }, $rows);

        $this->assertSame(['home'], $slugs);
    }

    #[Test]
    public function getEligibleDexWithAllOptions(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $options = new DexQueryOptions([
            'include_unreleased_dex' => true,
            'include_premium_dex' => true,
        ]);

        $rows = $service->getEligibleDex($options);

        $slugs = array_map(static function (array $row): string {
            /** @var scalar $slug */
            $slug = $row['slug'];

            return (string) $slug;
        }, $rows);

        $this->assertSame(['homepogo', 'home', 'redgreenblueyellow', 'spoon'], $slugs);
    }

    #[Test]
    public function getBatchKeyedByDexSlug(): void
    {
        $service = self::getContainer()->get(ElectionReportService::class);

        $reports = $service->getBatch(
            self::TRAINER_U12,
            ['home', 'redgreenblueyellow'],
            'favorite',
            5,
        );

        $this->assertArrayHasKey('home', $reports);
        $this->assertArrayHasKey('redgreenblueyellow', $reports);
        $this->assertCount(5, $reports['home']->top);
        $this->assertCount(5, $reports['redgreenblueyellow']->top);
    }
}

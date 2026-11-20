<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\ReportTrait;

/**
 * @psalm-import-type PokedexResponseReport from \App\Tests\Common\Types\PokedexTypes
 */
trait AssertReportTrait
{
    /**
     * @param PokedexResponseReport $report
     */
    protected function assertReport(
        array $report,
        int $countNo,
        int $countMaybe,
        int $countMaybeNot,
        int $countYes,
        int $countTotal
    ): void {
        $this->assertArrayHasKey('detail', $report);

        $reportDetail = $report['detail'];

        $this->assertArrayHasKey(0, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[0]);
        $this->assertSame($countNo, $reportDetail[0]['count']);
        $this->assertArrayHasKey('catch_state', $reportDetail[0]);
        $this->assertSame('no', $reportDetail[0]['catch_state']['slug']);
        $this->assertSame('No', $reportDetail[0]['catch_state']['name']);
        $this->assertSame('Non', $reportDetail[0]['catch_state']['french_name']);
        $this->assertSame('#ec8b8b', $reportDetail[0]['catch_state']['color']);

        $this->assertArrayHasKey(1, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[1]);
        $this->assertSame($countMaybe, $reportDetail[1]['count']);
        $this->assertArrayHasKey('catch_state', $reportDetail[1]);
        $this->assertSame('maybe', $reportDetail[1]['catch_state']['slug']);
        $this->assertSame('Maybe', $reportDetail[1]['catch_state']['name']);
        $this->assertSame('Peut être', $reportDetail[1]['catch_state']['french_name']);
        $this->assertSame('blue', $reportDetail[1]['catch_state']['color']);

        $this->assertArrayHasKey(2, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[2]);
        $this->assertSame($countMaybeNot, $reportDetail[2]['count']);
        $this->assertArrayHasKey('catch_state', $reportDetail[2]);
        $this->assertSame('maybenot', $reportDetail[2]['catch_state']['slug']);
        $this->assertSame('Maybe not', $reportDetail[2]['catch_state']['name']);
        $this->assertSame('Peut être pas', $reportDetail[2]['catch_state']['french_name']);
        $this->assertSame('yellow', $reportDetail[2]['catch_state']['color']);

        $this->assertArrayHasKey(3, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[3]);
        $this->assertSame($countYes, $reportDetail[3]['count']);
        $this->assertArrayHasKey('catch_state', $reportDetail[3]);
        $this->assertSame('yes', $reportDetail[3]['catch_state']['slug']);
        $this->assertSame('Yes', $reportDetail[3]['catch_state']['name']);
        $this->assertSame('Oui', $reportDetail[3]['catch_state']['french_name']);
        $this->assertSame('#256d29', $reportDetail[3]['catch_state']['color']);

        $this->assertArrayHasKey('total', $report);
        $this->assertSame($countTotal, $report['total']);

        $this->assertArrayHasKey('total_caught', $report);
        $this->assertSame($countYes, $report['total_caught']);

        $this->assertArrayHasKey('total_uncaught', $report);
        $this->assertSame($countTotal - $countMaybe - $countMaybeNot - $countYes, $report['total_uncaught']);
    }
}

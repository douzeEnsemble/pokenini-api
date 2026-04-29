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
        $this->assertEquals($countNo, $reportDetail[0]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[0]);
        $this->assertEquals('no', $reportDetail[0]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[0]);
        $this->assertEquals('No', $reportDetail[0]['name']);
        $this->assertArrayHasKey('french_name', $reportDetail[0]);
        $this->assertEquals('Non', $reportDetail[0]['french_name']);

        $this->assertArrayHasKey(1, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[1]);
        $this->assertEquals($countMaybe, $reportDetail[1]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[1]);
        $this->assertEquals('maybe', $reportDetail[1]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[1]);
        $this->assertEquals('Maybe', $reportDetail[1]['name']);
        $this->assertArrayHasKey('french_name', $reportDetail[1]);
        $this->assertEquals('Peut être', $reportDetail[1]['french_name']);

        $this->assertArrayHasKey(2, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[2]);
        $this->assertEquals($countMaybeNot, $reportDetail[2]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[2]);
        $this->assertEquals('maybenot', $reportDetail[2]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[2]);
        $this->assertEquals('Maybe not', $reportDetail[2]['name']);
        $this->assertArrayHasKey('french_name', $reportDetail[2]);
        $this->assertEquals('Peut être pas', $reportDetail[2]['french_name']);

        $this->assertArrayHasKey(3, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[3]);
        $this->assertEquals($countYes, $reportDetail[3]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[3]);
        $this->assertEquals('yes', $reportDetail[3]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[3]);
        $this->assertEquals('Yes', $reportDetail[3]['name']);
        $this->assertArrayHasKey('french_name', $reportDetail[3]);
        $this->assertEquals('Oui', $reportDetail[3]['french_name']);

        $this->assertArrayHasKey('total', $report);
        $this->assertEquals($countTotal, $report['total']);

        $this->assertArrayHasKey('total_caught', $report);
        $this->assertEquals($countYes, $report['total_caught']);

        $this->assertArrayHasKey('total_uncaught', $report);
        $this->assertEquals($countTotal - $countMaybe - $countMaybeNot - $countYes, $report['total_uncaught']);
    }
}

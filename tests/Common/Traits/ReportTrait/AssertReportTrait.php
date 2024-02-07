<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits\ReportTrait;

trait AssertReportTrait
{
    /**
     * @param int[]|int[][][]|string[][][] $report
     */
    protected function assertReport(
        array $report,
        int $countNo,
        int $countMaybe,
        int $countMaybeNot,
        int $countYes,
        int $countTotal
    ): void {
        $this->assertArrayHasKey('total', $report);
        $this->assertEquals($countTotal, $report['total']);

        $this->assertArrayHasKey('totalCaught', $report);
        $this->assertEquals($countYes, $report['totalCaught']);

        $this->assertArrayHasKey('totalUncaught', $report);
        $this->assertEquals($countTotal - $countMaybe - $countMaybeNot - $countYes, $report['totalUncaught']);

        $this->assertArrayHasKey('detail', $report);
        /** @var int[][]|string[][] $reportDetail */
        $reportDetail = $report['detail'];

        $this->assertArrayHasKey(0, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[0]);
        $this->assertEquals($countNo, $reportDetail[0]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[0]);
        $this->assertEquals('no', $reportDetail[0]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[0]);
        $this->assertEquals('No', $reportDetail[0]['name']);
        $this->assertArrayHasKey('frenchName', $reportDetail[0]);
        $this->assertEquals('Non', $reportDetail[0]['frenchName']);

        $this->assertArrayHasKey(1, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[1]);
        $this->assertEquals($countMaybe, $reportDetail[1]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[1]);
        $this->assertEquals('maybe', $reportDetail[1]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[1]);
        $this->assertEquals('Maybe', $reportDetail[1]['name']);
        $this->assertArrayHasKey('frenchName', $reportDetail[1]);
        $this->assertEquals('Peut être', $reportDetail[1]['frenchName']);

        $this->assertArrayHasKey(2, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[2]);
        $this->assertEquals($countMaybeNot, $reportDetail[2]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[2]);
        $this->assertEquals('maybenot', $reportDetail[2]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[2]);
        $this->assertEquals('Maybe not', $reportDetail[2]['name']);
        $this->assertArrayHasKey('frenchName', $reportDetail[2]);
        $this->assertEquals('Peut être pas', $reportDetail[2]['frenchName']);

        $this->assertArrayHasKey(3, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[3]);
        $this->assertEquals($countYes, $reportDetail[3]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[3]);
        $this->assertEquals('yes', $reportDetail[3]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[3]);
        $this->assertEquals('Yes', $reportDetail[3]['name']);
        $this->assertArrayHasKey('frenchName', $reportDetail[3]);
        $this->assertEquals('Oui', $reportDetail[3]['frenchName']);
    }
}

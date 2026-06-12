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
        $this->assertArrayHasKey('catch_state', $reportDetail[0]);
        $this->assertEquals('no', $reportDetail[0]['catch_state']['slug']);
        $this->assertEquals('No', $reportDetail[0]['catch_state']['name']);
        $this->assertEquals('Non', $reportDetail[0]['catch_state']['french_name']);
        $this->assertEquals('#e57373', $reportDetail[0]['catch_state']['color']);

        $this->assertArrayHasKey(1, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[1]);
        $this->assertEquals($countMaybe, $reportDetail[1]['count']);
        $this->assertArrayHasKey('catch_state', $reportDetail[1]);
        $this->assertEquals('maybe', $reportDetail[1]['catch_state']['slug']);
        $this->assertEquals('Maybe', $reportDetail[1]['catch_state']['name']);
        $this->assertEquals('Peut être', $reportDetail[1]['catch_state']['french_name']);
        $this->assertEquals('blue', $reportDetail[1]['catch_state']['color']);

        $this->assertArrayHasKey(2, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[2]);
        $this->assertEquals($countMaybeNot, $reportDetail[2]['count']);
        $this->assertArrayHasKey('catch_state', $reportDetail[2]);
        $this->assertEquals('maybenot', $reportDetail[2]['catch_state']['slug']);
        $this->assertEquals('Maybe not', $reportDetail[2]['catch_state']['name']);
        $this->assertEquals('Peut être pas', $reportDetail[2]['catch_state']['french_name']);
        $this->assertEquals('yellow', $reportDetail[2]['catch_state']['color']);

        $this->assertArrayHasKey(3, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[3]);
        $this->assertEquals($countYes, $reportDetail[3]['count']);
        $this->assertArrayHasKey('catch_state', $reportDetail[3]);
        $this->assertEquals('yes', $reportDetail[3]['catch_state']['slug']);
        $this->assertEquals('Yes', $reportDetail[3]['catch_state']['name']);
        $this->assertEquals('Oui', $reportDetail[3]['catch_state']['french_name']);
        $this->assertEquals('#66bb6a', $reportDetail[3]['catch_state']['color']);

        $this->assertArrayHasKey('total', $report);
        $this->assertEquals($countTotal, $report['total']);

        $this->assertArrayHasKey('total_caught', $report);
        $this->assertEquals($countYes, $report['total_caught']);

        $this->assertArrayHasKey('total_uncaught', $report);
        $this->assertEquals($countTotal - $countMaybe - $countMaybeNot - $countYes, $report['total_uncaught']);
    }
}

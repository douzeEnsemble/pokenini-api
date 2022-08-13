<?php

namespace App\Tests\Resources\Traits;

use PHPUnit\Framework\Assert;

trait AssertReportTrait
{
    /**
     * @param string[][]|int[][] $report
     */
    public function assertReport(array $report, int $countNo, int $countMaybe, int $countMaybeNot, int $countYes): void
    {
        Assert::assertArrayHasKey('total', $report);
        Assert::assertEquals(
            array_sum([
                $countNo,
                $countMaybe,
                $countMaybeNot,
                $countYes,
            ]),
            $report['total']
        );
        Assert::assertArrayHasKey('caught', $report);
        Assert::assertEquals($countYes, $report['caught']);

        Assert::assertArrayHasKey('detail', $report);
        $reportDetail = $report['detail'];

        Assert::assertArrayHasKey(0, $reportDetail);
        Assert::assertArrayHasKey('count', $reportDetail[0]);
        Assert::assertEquals($countNo, $reportDetail[0]['count']);
        Assert::assertArrayHasKey('slug', $reportDetail[0]);
        Assert::assertEquals('no', $reportDetail[0]['slug']);
        Assert::assertArrayHasKey('name', $reportDetail[0]);
        Assert::assertEquals('No', $reportDetail[0]['name']);
        Assert::assertArrayHasKey('french_name', $reportDetail[0]);
        Assert::assertEquals('Non', $reportDetail[0]['french_name']);

        Assert::assertArrayHasKey(1, $reportDetail);
        Assert::assertArrayHasKey('count', $reportDetail[1]);
        Assert::assertEquals($countMaybe, $reportDetail[1]['count']);
        Assert::assertArrayHasKey('slug', $reportDetail[1]);
        Assert::assertEquals('maybe', $reportDetail[1]['slug']);
        Assert::assertArrayHasKey('name', $reportDetail[1]);
        Assert::assertEquals('Maybe', $reportDetail[1]['name']);
        Assert::assertArrayHasKey('french_name', $reportDetail[1]);
        Assert::assertEquals('Peut être', $reportDetail[1]['french_name']);

        Assert::assertArrayHasKey(2, $reportDetail);
        Assert::assertArrayHasKey('count', $reportDetail[2]);
        Assert::assertEquals($countMaybeNot, $reportDetail[2]['count']);
        Assert::assertArrayHasKey('slug', $reportDetail[2]);
        Assert::assertEquals('maybenot', $reportDetail[2]['slug']);
        Assert::assertArrayHasKey('name', $reportDetail[2]);
        Assert::assertEquals('Maybe not', $reportDetail[2]['name']);
        Assert::assertArrayHasKey('french_name', $reportDetail[2]);
        Assert::assertEquals('Peut être pas', $reportDetail[2]['french_name']);

        Assert::assertArrayHasKey(3, $reportDetail);
        Assert::assertArrayHasKey('count', $reportDetail[3]);
        Assert::assertEquals($countYes, $reportDetail[3]['count']);
        Assert::assertArrayHasKey('slug', $reportDetail[3]);
        Assert::assertEquals('yes', $reportDetail[3]['slug']);
        Assert::assertArrayHasKey('name', $reportDetail[3]);
        Assert::assertEquals('Yes', $reportDetail[3]['name']);
        Assert::assertArrayHasKey('french_name', $reportDetail[3]);
        Assert::assertEquals('Oui', $reportDetail[3]['french_name']);
    }
}

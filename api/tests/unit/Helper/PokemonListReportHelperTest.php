<?php

namespace App\Tests\Unit\Helper;

use App\Entity\CatchState;
use App\Helper\PokedexListReportHelper;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class PokemonListReportHelperTest extends TestCase
{
    public function testGetReportFromPokemonList(): void
    {
        $this->assertReportFromFilename('all_null', 9, 0, 0, 0);
        $this->assertReportFromFilename('no_yes', 4, 2, 3, 0);
        $this->assertReportFromFilename('all_yes', 0, 0, 0, 9);
        $this->assertReportFromFilename('all_set', 3, 2, 3, 1);
    }

    private function assertReportFromFilename(
        string $filename,
        int $countNo,
        int $countMaybe,
        int $countMaybeNot,
        int $countYes
    ): void {
        $report = PokedexListReportHelper::getReportFromPokedex(
            $this->getPokedexFromFile($filename),
            [
                $this->getCatchState('no', 'No', 'Non'),
                $this->getCatchState('maybe', 'Maybe', 'Peut être'),
                $this->getCatchState('maybenot', 'Maybe not', 'Peut être pas'),
                $this->getCatchState('yes', 'Yes', 'Oui'),
            ]
        );

        $this->assertReport($report, $countNo, $countMaybe, $countMaybeNot, $countYes);
    }

    /**
     * @param string[][]|int[][] $report
     */
    public function assertReport(
        array $report,
        int $countNo,
        int $countMaybe,
        int $countMaybeNot,
        int $countYes
    ): void {
        Assert::assertArrayHasKey('no', $report);
        Assert::assertArrayHasKey('count', $report['no']);
        Assert::assertEquals($countNo, $report['no']['count']);
        Assert::assertArrayHasKey('name', $report['no']);
        Assert::assertEquals('No', $report['no']['name']);
        Assert::assertArrayHasKey('french_name', $report['no']);
        Assert::assertEquals('Non', $report['no']['french_name']);

        Assert::assertArrayHasKey('maybe', $report);
        Assert::assertArrayHasKey('count', $report['maybe']);
        Assert::assertEquals($countMaybe, $report['maybe']['count']);
        Assert::assertArrayHasKey('name', $report['maybe']);
        Assert::assertEquals('Maybe', $report['maybe']['name']);
        Assert::assertArrayHasKey('french_name', $report['maybe']);
        Assert::assertEquals('Peut être', $report['maybe']['french_name']);

        Assert::assertArrayHasKey('maybenot', $report);
        Assert::assertArrayHasKey('count', $report['maybenot']);
        Assert::assertEquals($countMaybeNot, $report['maybenot']['count']);
        Assert::assertArrayHasKey('name', $report['maybenot']);
        Assert::assertEquals('Maybe not', $report['maybenot']['name']);
        Assert::assertArrayHasKey('french_name', $report['maybenot']);
        Assert::assertEquals('Peut être pas', $report['maybenot']['french_name']);

        Assert::assertArrayHasKey('yes', $report);
        Assert::assertArrayHasKey('count', $report['yes']);
        Assert::assertEquals($countYes, $report['yes']['count']);
        Assert::assertArrayHasKey('name', $report['yes']);
        Assert::assertEquals('Yes', $report['yes']['name']);
        Assert::assertArrayHasKey('french_name', $report['yes']);
        Assert::assertEquals('Oui', $report['yes']['french_name']);
    }

    /**
     * @return string[][]|int[][]
     */
    private function getPokedexFromFile(string $filename): array
    {
        $fileEndPath = '/resources/data/pokedex_list/' . $filename . '.json';
        $filePath = dirname(__DIR__, 2) . $fileEndPath;
        $fileContent = file_get_contents($filePath);

        if (!$fileContent) {
            throw new \RuntimeException("File '$fileEndPath' can't be read");
        }

        /** @var string[][]|int[][] */
        return \json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);
    }

    private function getCatchState(string $slug, string $name, string $frenchName): CatchState
    {
        $catchState = new CatchState();

        $catchState->slug = $slug;
        $catchState->name = $name;
        $catchState->frenchName = $frenchName;

        return $catchState;
    }
}

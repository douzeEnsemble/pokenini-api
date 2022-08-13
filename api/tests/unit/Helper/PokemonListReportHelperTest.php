<?php

namespace App\Tests\Unit\Helper;

use App\Entity\CatchState;
use App\Helper\PokedexListReportHelper;
use App\Tests\Resources\Traits\AssertReportTrait;
use PHPUnit\Framework\TestCase;

class PokemonListReportHelperTest extends TestCase
{
    use AssertReportTrait;

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
